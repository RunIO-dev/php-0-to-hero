<?php

// return hands a value back to the code that called the function.
function lateFee($daysLate)
{
    return $daysLate * 50;
}

$fee = lateFee(3);
echo "Fee: $fee cents\n";

// A call is a value, so it can go wherever a value can.
echo 'Two books, 4 days late: ', lateFee(4) * 2, " cents\n";
