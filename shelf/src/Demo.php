<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Shelf\Web\Covers;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Shelf as a demo (chapter 88): anyone may log in, as a member of staff or as a reader, and try
 * everything. Every night, reset() puts the library back as it was: the books of books.json and
 * catalogue.json, the members of members.json, and a few loans and reservations, so the desk and
 * the dashboard have something to show. On with SHELF_DEMO=1; never on a library's real data.
 */
final readonly class Demo
{
    /** The shared password of the demo accounts. It is shown on the login page: it protects nothing. */
    public const string PASSWORD = 'shelf demo for everyone';

    /** The accounts the login page offers. Nobody may change or remove them, so each visitor finds them. */
    public const array ACCOUNTS = ['Jonas' => Role::Staff, 'Amira' => Role::Reader];

    public function __construct(
        #[Autowire('%env(bool:SHELF_DEMO)%')]
        public bool $on,
        private Connection $db,
        private EntityManagerInterface $entities,
        private BookRepository $books,
        private MemberRepository $members,
        private Library $library,
        private Accounts $accounts,
        private Covers $covers,
        #[Autowire('%kernel.project_dir%')]
        private string $dir,
    ) {}

    /** True for the accounts every visitor shares, while Shelf is a demo. */
    public function isShared(string $name): bool
    {
        return $this->on && array_any(array_keys(self::ACCOUNTS), fn(string $shared): bool => mb_strtolower($shared) === mb_strtolower(trim($name)));
    }

    /**
     * Everything back to the start, in one transaction: a visitor who looks during the reset sees
     * the old library or the new one, never half of each.
     *
     * @return array{books: int, members: int} what the demo has now
     */
    public function reset(DateTimeImmutable $today): array
    {
        if (!$this->on) {
            throw new LogicException('Shelf is not a demo (SHELF_DEMO=0): a reset would delete every book and member.');
        }
        $books = array_map(Book::fromArray(...), [...readList("$this->dir/books.json"), ...readList("$this->dir/catalogue.json")]);
        $members = loadMembers("$this->dir/members.json");
        foreach ($this->books->all() as $book) {
            $this->covers->remove($book->title); // files, outside the transaction: a cover nobody shows is harmless
        }
        $this->entities->clear(); // Doctrine forgets the objects it has loaded; the rows go next
        $this->entities->wrapInTransaction(function () use ($books, $members, $today): void {
            // Children first, because of the foreign keys (chapter 56).
            foreach (['DELETE FROM holds', 'DELETE FROM loans', 'DELETE FROM api_tokens', 'DELETE FROM messenger_messages', 'DELETE FROM members', 'DELETE FROM books'] as $sql) {
                $this->db->executeStatement($sql);
            }
            array_walk($books, $this->books->add(...));
            array_walk($members, $this->members->add(...));
            foreach (array_keys(self::ACCOUNTS) as $name) {
                $this->accounts->setPassword($name, self::PASSWORD);
            }
            $this->entities->flush();
            $this->activity($today);
        });

        return ['books' => count($books), 'members' => count($members)];
    }

    /** A library in use: a book overdue, one due soon, one returned, and a reader waiting for a copy. */
    private function activity(DateTimeImmutable $today): void
    {
        $this->library->lend('Things Fall Apart', 'Kenji', $today->modify('-30 days'));
        $this->library->lend('Things Fall Apart', 'Amira', $today->modify('-12 days'));
        $this->library->lend('Kitchen', 'Ana', $today->modify('-10 days'));
        $returned = $this->library->lend('Frankenstein', 'Amira', $today->modify('-40 days'));
        $this->library->giveBack($returned->id(), $today->modify('-25 days'));
        $this->library->reserve('Kitchen', $this->library->findMember('Amira') ?? throw new LogicException('members.json has no Amira'), $today->modify('-2 days'));
    }
}
