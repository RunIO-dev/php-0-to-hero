<?php

$perDay = 50;

// ✗ Don't do this: global reaches out of the function for a variable.
function lateFee($daysLate)
{
    global $perDay;

    return $daysLate * $perDay;
}

echo lateFee(3), "\n";
