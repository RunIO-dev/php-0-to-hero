<?php

// Money is counted in cents, as an int: whole numbers are exact.
$feePerDayCents = 25;
$daysLate = 3;

$feeCents = $feePerDayCents * $daysLate;
echo "Late fee: $feeCents cents\n";
echo 'Late fee: ', number_format($feeCents / 100, 2), "\n";
