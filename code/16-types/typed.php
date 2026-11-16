<?php

declare(strict_types=1);

// int before the parameter: $daysLate must be an int.
// : int after the brackets: the function returns an int.
function lateFee(int $daysLate): int
{
    return $daysLate * 50;
}

echo 'Fee: ', lateFee(3), " cents\n";
