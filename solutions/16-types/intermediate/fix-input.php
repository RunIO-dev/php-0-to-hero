<?php

declare(strict_types=1);

function lateFee(int $daysLate): int
{
    return $daysLate * 50;
}

$input = '4';
$daysLate = (int) $input;

echo 'Fee: ', lateFee($daysLate), " cents\n";
