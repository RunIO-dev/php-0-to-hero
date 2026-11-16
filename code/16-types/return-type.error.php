<?php

declare(strict_types=1);

function lateFee(int $daysLate): int
{
    if ($daysLate <= 0) {
        return 'free';
    }

    return $daysLate * 50;
}

echo lateFee(0), "\n";
