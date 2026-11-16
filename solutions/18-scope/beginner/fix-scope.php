<?php

declare(strict_types=1);

$perDay = 50;

function lateFee(int $daysLate, int $perDay): int
{
    return $daysLate * $perDay;
}

echo 'Fee: ', lateFee(3, $perDay), " cents\n";
