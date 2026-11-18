<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;

/** Where the loans are kept (chapter 56): which book is lent to whom, until when. */
interface LoanRepository
{
    /**
     * Stores the loan and takes one copy of its book off the shelf: both, or neither (chapter 57).
     * Throws NoCopyLeft if no copy is left, even when another request took the last one a moment ago.
     * Chapter 84: $hold, the member's open reservation of the book, is collected with it; a copy set
     * aside for it is off the shelf already, so none is taken.
     */
    public function add(Loan $loan, ?Hold $hold = null): void;

    /** @return list<Loan> the member's loans that aren't back yet, the soonest due first */
    public function forMember(string $name): array;

    /** Whether a copy of the book is out on a loan that isn't back yet. */
    public function isOnLoan(string $title): bool;

    /** Chapter 83: a loan by its number, back or not; null if there is none. */
    public function find(int $id): ?Loan;

    /** @return list<Loan> chapter 83: every loan that isn't back yet, the soonest due first */
    public function current(): array;

    /**
     * Chapter 83: the loan is back, and its copy on the shelf again: both, or neither.
     * Chapter 84: or set aside for the first in the book's queue, whose reservation is returned.
     */
    public function giveBack(Loan $loan, DateTimeImmutable $today): ?Hold;

    /** Chapter 83: stores a change to a loan (its new due date). */
    public function update(Loan $loan): void;

    /**
     * Chapter 85: notes that a reminder about the loan goes out today, but only if its last reminder is
     * still $seen, the one the caller read. One UPDATE that checks it: if another worker sent a reminder
     * since, no row changes, and the answer is false: don't send it again.
     */
    public function markReminded(Loan $loan, ?DateTimeImmutable $seen, DateTimeImmutable $today): bool;

    /** Chapter 83: deletes the member's returned loans, when the member deletes their account. */
    public function forget(Member $member): void;

    /** Chapter 83: deletes the book's returned loans, when the book is removed for good. */
    public function forgetBook(Book $book): void;
}
