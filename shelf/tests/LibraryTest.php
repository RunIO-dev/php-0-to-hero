<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\BookExists;
use Shelf\BookNotFound;
use Shelf\BookQuery;
use Shelf\Library;
use Shelf\LoanNotFound;
use Shelf\Loan;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\NoCopyLeft;
use Shelf\Role;

final class LibraryTest extends TestCase
{
    private InMemoryBookRepository $books;
    private InMemoryLoanRepository $loans;
    private Library $library;

    protected function setUp(): void
    {
        $this->books = new InMemoryBookRepository([
            new Book('Kindred', 'Octavia E. Butler', 1979, copies: 1),
            new Book('Things Fall Apart', 'Chinua Achebe', 1958, copies: 2),
        ]);
        $this->loans = new InMemoryLoanRepository($this->books);
        $this->library = new Library($this->books, new InMemoryMemberRepository([new Member('Amira', Role::Reader)]), $this->loans);
    }

    public function testLendingSavesOneCopyLess(): void
    {
        $this->library->lend('things fall apart', 'amira', new DateTimeImmutable('2026-03-02'));

        $book = $this->library->books()[1];
        self::assertSame('Things Fall Apart', $book->title);
        self::assertSame(1, $book->copies);
    }

    public function testTheLastCopyCanBeLentOnlyOnce(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $this->expectException(NoCopyLeft::class);
        $this->expectExceptionMessage('All copies of "Kindred" are on loan');

        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
    }

    public function testAnUnknownBookCantBeLent(): void
    {
        $this->expectException(BookNotFound::class);
        $this->expectExceptionMessage('There is no book titled "Dune"');

        $this->library->lend('Dune', 'Amira', new DateTimeImmutable('2026-03-02'));
    }

    public function testAnUnknownMemberCantBorrow(): void
    {
        $this->expectException(MemberNotFound::class);

        $this->library->lend('Kindred', 'Nobody', new DateTimeImmutable('2026-03-02'));
    }

    public function testATitleCanBeAddedOnlyOnceInAnyCase(): void
    {
        $this->expectException(BookExists::class);
        $this->expectExceptionMessage('"Kindred" is already on the shelf');

        $this->library->add(['  KINDRED ', 'Octavia E. Butler', '1979']);
    }

    public function testAnAddedBookIsSavedInOrder(): void
    {
        $this->library->add(['Beloved', 'Toni Morrison', '1987']);

        self::assertSame(['Beloved', 'Kindred', 'Things Fall Apart'], array_column($this->library->books(), 'title'));
    }

    public function testTheCopiesCanBeChanged(): void
    {
        $this->library->changeCopies('kindred', '3');

        self::assertSame(3, $this->library->find('Kindred')->copies);
    }

    /** @return iterable<string, array{string}> */
    public static function wrongCopies(): iterable
    {
        yield 'less than none' => ['-1'];
        yield 'not a number' => ['two'];
        yield 'a fraction' => ['1.5'];
        yield 'too many' => ['1000'];
        yield 'nothing' => [''];
    }

    #[DataProvider('wrongCopies')]
    public function testTheCopiesMustBeAWholeNumberInRange(string $copies): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Copies must be a whole number from 0 to 999.');

        $this->library->changeCopies('Kindred', $copies);
    }

    public function testARemovedBookIsGone(): void
    {
        $removed = $this->library->remove('KINDRED');

        self::assertSame('Kindred', $removed->title);
        self::assertSame(['Things Fall Apart'], array_column($this->library->books(), 'title'));
    }

    public function testAnUnknownBookCantBeRemoved(): void
    {
        $this->expectException(BookNotFound::class);

        $this->library->remove('Dune');
    }

    public function testALoanIsStoredForTheMember(): void
    {
        $this->library->lend('Things Fall Apart', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-01'));

        $loans = $this->library->loansOf('amira');

        self::assertSame(['Kindred', 'Things Fall Apart'], array_map(fn(Loan $loan): string => $loan->book->title, $loans));
        self::assertSame('2026-03-15', $loans[0]->due->format('Y-m-d'));
    }

    public function testOnlyAMemberHasLoans(): void
    {
        $this->expectException(MemberNotFound::class);

        $this->library->loansOf('Nobody');
    }

    public function testABookOnLoanCantBeRemoved(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Kindred" is on loan, so it can\'t be removed.');

        $this->library->remove('Kindred');
    }

    /** Chapter 83: a book that came back can go, and its history with it. */
    public function testABookThatCameBackCanBeRemoved(): void
    {
        $loan = $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->giveBack($loan->id(), new DateTimeImmutable('2026-03-05'));

        $this->library->remove('Kindred');

        self::assertSame([], $this->library->loansOf('Amira'));
        self::assertSame(['Things Fall Apart'], array_column($this->library->books(), 'title'));
    }

    /** Chapter 82: a member deletes their account. */
    public function testAMemberCanLeave(): void
    {
        $this->library->removeMember($this->library->findMember('Amira') ?? self::fail('No Amira'));

        self::assertNull($this->library->findMember('Amira'));
    }

    public function testAMemberWithBooksCantLeave(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->lend('Things Fall Apart', 'Amira', new DateTimeImmutable('2026-03-02'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please return your 2 books first: then you can delete your account.');
        $this->library->removeMember($this->library->findMember('Amira') ?? self::fail('No Amira'));
    }

    /** Chapter 83: a blocked member can't borrow. */
    public function testABlockedMemberCantBorrow(): void
    {
        $staff = new Member('Jonas', Role::Staff);
        $this->library->setBlocked('Amira', true, $staff);

        try {
            $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
            self::fail('Lent to a blocked member');
        } catch (InvalidArgumentException $e) {
            self::assertSame("Amira is blocked, and can't borrow books.", $e->getMessage());
        }
        $this->library->setBlocked('amira', false, $staff);
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        self::assertCount(1, $this->library->loansOf('Amira'));
    }

    public function testAReaderHasFiveBooksAtMost(): void
    {
        foreach (range(1, 6) as $n) {
            $this->books->add(new Book("Story $n", 'Ana Writer', 2000 + $n));
        }
        foreach (range(1, 5) as $n) {
            $this->library->lend("Story $n", 'Amira', new DateTimeImmutable('2026-03-02'));
        }

        $this->expectExceptionMessage('Amira has 5 books already: that is the most a reader may have.');
        $this->library->lend('Story 6', 'Amira', new DateTimeImmutable('2026-03-02'));
    }

    public function testAReturnedBookCanBeLentAgain(): void
    {
        $loan = $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->giveBack($loan->id(), new DateTimeImmutable('2026-03-05'));

        self::assertSame([], $this->library->currentLoans());
        self::assertSame(1, $this->library->find('Kindred')->copies);
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-05'));
    }

    public function testNobodyChangesTheirOwnRole(): void
    {
        $this->expectExceptionMessage("You can't change your own account here");

        $this->library->changeRole('AMIRA', Role::Staff, $this->library->findMember('Amira') ?? self::fail('No Amira'));
    }

    public function testAnUnknownLoanIsNotFound(): void
    {
        $this->expectException(LoanNotFound::class);

        $this->library->giveBack(42, new DateTimeImmutable('2026-03-05'));
    }

    public function testAPageKnowsHowManyPagesThereAre(): void
    {
        $first = $this->library->page(new BookQuery(), 1, 1);
        $last = $this->library->page(new BookQuery(), 2, 1);

        self::assertSame([['Kindred'], 2, 2], [array_column($first['books'], 'title'), $first['pages'], $first['total']]);
        self::assertSame(['Things Fall Apart'], array_column($last['books'], 'title'));
        self::assertSame(['books' => [], 'pages' => 1, 'total' => 0], $this->library->page(new BookQuery('Morrison'), 1, 20), 'Nothing found is still one page');
    }

    public function testPagesCountFromOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->library->page(new BookQuery(), 0, 20);
    }
}
