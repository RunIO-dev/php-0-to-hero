<?php

declare(strict_types=1);

// Exercise 17, beginner: the usual fee.
//
// Most books cost 50 cents per day late; only a few cost something else.
// Give $perDay the default value 50, so that both calls work:
//   lateFee(3)      returns 150
//   lateFee(3, 20)  returns 60
//
// Check your work: composer exercise 17 beginner

function lateFee(int $daysLate, int $perDay): int
{
    return $daysLate * $perDay;
}
