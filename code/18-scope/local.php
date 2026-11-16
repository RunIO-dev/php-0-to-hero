<?php

declare(strict_types=1);

function lateFee(int $daysLate): int
{
    // $perDay exists only inside this function, and only while it runs.
    $perDay = 50;

    return $daysLate * $perDay;
}

echo lateFee(3), " cents\n";
// Out here, there is no $perDay. The common mistakes show what happens if you try.
