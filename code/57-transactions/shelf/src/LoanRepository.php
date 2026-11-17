<?php

declare(strict_types=1);

namespace Shelf;

/** Where the loans are kept (chapter 56): which book is lent to whom, until when. */
interface LoanRepository
{
    /**
     * Stores the loan and takes one copy of its book off the shelf: both, or neither (chapter 57).
     * Throws NoCopyLeft if no copy is left, even when another request took the last one a moment ago.
     */
    public function add(Loan $loan): void;

    /** @return list<Loan> the member's loans, the soonest due first */
    public function forMember(string $name): array;

    public function isOnLoan(string $title): bool;
}
