<?php

declare(strict_types=1);

namespace Shelf\Tests;

use Shelf\Loan;
use Shelf\LoanRepository;

use function Shelf\sameTitle;

/** Loans for tests, in an array (chapter 56). */
final class InMemoryLoanRepository implements LoanRepository
{
    /** @var list<Loan> */
    private array $loans = [];

    public function add(Loan $loan): void
    {
        $this->loans[] = $loan;
    }

    public function forMember(string $name): array
    {
        $found = array_values(array_filter($this->loans, fn(Loan $loan): bool => mb_strtolower($loan->member->name) === mb_strtolower(trim($name))));
        usort($found, fn(Loan $a, Loan $b): int => [$a->due, $a->book->title] <=> [$b->due, $b->book->title]);

        return $found;
    }

    public function isOnLoan(string $title): bool
    {
        return array_any($this->loans, fn(Loan $loan): bool => sameTitle($loan->book->title, $title));
    }
}
