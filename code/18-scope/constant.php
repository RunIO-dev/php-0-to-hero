<?php

declare(strict_types=1);

// A constant has one value for the whole program, and functions can read it.
const FEE_PER_DAY = 50;
const LOAN_DAYS = 14;

function lateFee(int $daysLate): int
{
    return $daysLate * FEE_PER_DAY;
}

echo 'Loans last ', LOAN_DAYS, " days.\n";
echo 'Fee: ', lateFee(3), " cents\n";
