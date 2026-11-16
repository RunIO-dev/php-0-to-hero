<?php

declare(strict_types=1);

// What a function needs comes in through its parameters.
function lateFee(int $daysLate, int $perDay): int
{
    return $daysLate * $perDay;
}

$perDay = 50;
echo 'Fee: ', lateFee(3, $perDay), " cents\n";
