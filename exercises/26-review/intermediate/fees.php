<?php

declare(strict_types=1);

// Exercise 26, intermediate: one rule, one place.
//
// The library's rule: 50 cents per day late, at most 1000 cents for one book.
// Three functions use the rule, and their copies have drifted apart: one has
// forgotten the maximum, and one charges for a book that isn't late.
// Write a function lateFee(int $daysLate): int with the rule, use it in all
// three, and write 50 and 1000 only once each (as constants, if you like).
//
// Check your work: composer exercise 26 intermediate

function receiptLine(string $title, int $daysLate): string
{
    $fee = min($daysLate * 50, 1000);

    return "$title: $fee cents";
}

function reminder(string $member, int $daysLate): string
{
    $fee = $daysLate * 50;

    return "Dear $member, your fee is now $fee cents.";
}

/** @param list<int> $daysLate one number per book */
function totalFees(array $daysLate): int
{
    $total = 0;
    foreach ($daysLate as $days) {
        $total += min(abs($days) * 50, 1000);
    }

    return $total;
}
