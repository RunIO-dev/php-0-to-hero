<?php

declare(strict_types=1);

function lateFee(int $daysLate): int
{
    return $daysLate * 50;
}

// '3' is a string, not an int.
echo lateFee('3'), "\n";
