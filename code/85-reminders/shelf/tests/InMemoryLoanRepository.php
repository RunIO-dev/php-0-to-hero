<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\Hold;
use Shelf\HoldStatus;
use Shelf\Loan;
use Shelf\Member;
use Shelf\LoanRepository;

use function Shelf\sameTitle;

/** Loans for tests, in an array (chapter 56). Lending takes a copy from the books it was given (57). */
final class InMemoryLoanRepository implements LoanRepository
{
    /** @var list<Loan> */
    private array $loans = [];

    private readonly InMemoryHoldRepository $holds;

    /** Chapter 84: with the reservations, which a copy that comes back goes to first. */
    public function __construct(private readonly InMemoryBookRepository $books, ?InMemoryHoldRepository $holds = null)
    {
        $this->holds = $holds ?? new InMemoryHoldRepository($books);
    }

    public function add(Loan $loan, ?Hold $hold = null): void
    {
        if ($hold?->status !== HoldStatus::Ready) {
            $book = $this->books->find($loan->book->title) ?? throw new BookNotFound("There is no book titled \"{$loan->book->title}\"");
            $book->lend();
            $this->books->update($book);
        }
        $hold?->collect();
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

    public function giveBack(Loan $loan, DateTimeImmutable $today): ?Hold
    {
        $loan->giveBack($today);

        return $this->holds->passOn($loan->book, $today);
    }

    public function update(Loan $loan): void {}

    public function markReminded(Loan $loan, ?DateTimeImmutable $seen, DateTimeImmutable $today): bool
    {
        if ($loan->remindedOn?->format('Y-m-d') !== $seen?->format('Y-m-d')) {
            return false;
        }
        $loan->remind($today);

        return true;
    }

    public function forget(Member $member): void
    {
        $this->loans = array_values(array_filter($this->loans, fn(Loan $loan): bool => $loan->member !== $member || !$loan->isReturned()));
    }

    public function forgetBook(Book $book): void
    {
        $this->loans = array_values(array_filter($this->loans, fn(Loan $loan): bool => !sameTitle($loan->book->title, $book->title) || !$loan->isReturned()));
    }
}
