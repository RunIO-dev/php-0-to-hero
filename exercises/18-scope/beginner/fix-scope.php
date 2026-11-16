<?php

declare(strict_types=1);

// Exercise 18, beginner: what the function needs.
//
// Run this file: PHP warns about an undefined variable, and the fee is 0.
// Pass $perDay to lateFee() as a second parameter, so that it prints
//   Fee: 150 cents
//
// Check your work: composer exercise 18 beginner

$perDay = 50;

function lateFee(int $daysLate): int
{
    return $daysLate * $perDay;
}

echo 'Fee: ', lateFee(3), " cents\n";
