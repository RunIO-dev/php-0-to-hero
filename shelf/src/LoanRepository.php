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
     */
    public function add(Loan $loan): void;

    /** @return list<Loan> the member's loans that aren't back yet, the soonest due first */
    public function forMember(string $name): array;

    /** Whether a copy of the book is out on a loan that isn't back yet. */
    public function isOnLoan(string $title): bool;

    /** Chapter 83: a loan by its number, back or not; null if there is none. */
    public function find(int $id): ?Loan;

    /** @return list<Loan> chapter 83: every loan that isn't back yet, the soonest due first */
    public function current(): array;

    /** Chapter 83: the loan is back, and its copy on the shelf again: both, or neither. */
    public function giveBack(Loan $loan, DateTimeImmutable $today): void;

    /** Chapter 83: stores a change to a loan (its new due date). */
    public function update(Loan $loan): void;

    /** Chapter 83: deletes the member's returned loans, when the member deletes their account. */
    public function forget(Member $member): void;

    /** Chapter 83: deletes the book's returned loans, when the book is removed for good. */
    public function forgetBook(Book $book): void;
}
