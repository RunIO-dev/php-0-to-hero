<?php

declare(strict_types=1);

// Exercise 35, beginner: fill the gap of an abstract class.
//
// The abstract class LateFee is given. Write two classes that extend it:
// - BookFee: 50 cents per day late;
// - DvdFee: 100 cents per day late.
// Each only fills the gap: perDay().

abstract class LateFee
{
    final public function forDays(int $days): int
    {
        return max(0, $days) * $this->perDay();
    }

    abstract protected function perDay(): int;
}

// Check your work: composer exercise 35 beginner
