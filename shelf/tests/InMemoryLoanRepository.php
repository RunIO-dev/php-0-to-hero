<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use Shelf\BookNotFound;
use Shelf\Loan;
use Shelf\Member;
use Shelf\LoanRepository;

use function Shelf\sameTitle;

/** Loans for tests, in an array (chapter 56). Lending takes a copy from the books it was given (57). */
final class InMemoryLoanRepository implements LoanRepository
{
    /** @var list<Loan> */
    private array $loans = [];

    public function __construct(private readonly InMemoryBookRepository $books) {}

    public function add(Loan $loan): void
    {
        $book = $this->books->find($loan->book->title) ?? throw new BookNotFound("There is no book titled \"{$loan->book->title}\"");
        $book->lend();
        $this->books->update($book);
        $this->loans[] = $loan;
        new \ReflectionProperty(Loan::class, 'id')->setValue($loan, count($this->loans)); // as the database would
    }

    public function forMember(string $name): array
    {
        return array_values(array_filter($this->current(), fn(Loan $loan): bool => mb_strtolower($loan->member->name) === mb_strtolower(trim($name))));
    }

    public function isOnLoan(string $title): bool
    {
        return array_any($this->current(), fn(Loan $loan): bool => sameTitle($loan->book->title, $title));
    }

    public function find(int $id): ?Loan
    {
        return array_find($this->loans, fn(Loan $loan): bool => $loan->id() === $id);
    }

    public function current(): array
    {
        $found = array_values(array_filter($this->loans, fn(Loan $loan): bool => !$loan->isReturned()));
        usort($found, fn(Loan $a, Loan $b): int => [$a->due, $a->book->title, $a->member->name] <=> [$b->due, $b->book->title, $b->member->name]);

        return $found;
    }

    public function giveBack(Loan $loan, DateTimeImmutable $today): void
    {
        $loan->giveBack($today);
        $book = $this->books->find($loan->book->title) ?? throw new BookNotFound("There is no book titled \"{$loan->book->title}\"");
        $book->putBack();
        $this->books->update($book);
    }

    public function update(Loan $loan): void {}

    public function forget(Member $member): void
    {
        $this->loans = array_values(array_filter($this->loans, fn(Loan $loan): bool => $loan->member !== $member || !$loan->isReturned()));
    }
}
