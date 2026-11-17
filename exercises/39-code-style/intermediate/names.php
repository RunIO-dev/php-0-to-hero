<?php

declare(strict_types=1);

// Exercise 39, intermediate: names as PSR-1 asks for them.
//
// The layout is fine, the names are not. Rename, and change every place that uses them:
// - the class: StudlyCaps, LateFeeCalculator;
// - the constants: UPPER_CASE, CENTS_PER_DAY and MAX_FINE;
// - the methods: camelCase, calculate and isFree.

final class late_fee_calculator
{
    public const int centsPerDay = 20;
    public const int max_fine = 500;

    public function Calculate(int $days): int
    {
        return $this->is_free($days) ? 0 : min($days * self::centsPerDay, self::max_fine);
    }

    public function is_free(int $days): bool
    {
        return $days <= 0;
    }
}

// Check your work: composer exercise 39 intermediate
