<?php

declare(strict_types=1);

// = 50 gives $perDay a default value: the call may leave it out.
function lateFee(int $daysLate, int $perDay = 50): int
{
    return $daysLate * $perDay;
}

echo lateFee(3), " cents\n";      // $perDay is 50
echo lateFee(3, 20), " cents\n";  // $perDay is 20
