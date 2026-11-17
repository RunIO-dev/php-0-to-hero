<?php

declare(strict_types=1);

namespace Shelf;

/** Where the loans are kept (chapter 56): which book is lent to whom, until when. */
interface LoanRepository
{
    public function add(Loan $loan): void;

    /** @return list<Loan> the member's loans, the soonest due first */
    public function forMember(string $name): array;

    public function isOnLoan(string $title): bool;
}
