<?php

// The function prints the fee instead of returning it.
function lateFee($daysLate)
{
    echo $daysLate * 50;
}

$fee = lateFee(3);
echo "\nFee: $fee cents\n";
