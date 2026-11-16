<?php

$daysLate = 4;
$feePerDayCents = 35;

$feeCents = $daysLate * $feePerDayCents;
echo 'Late fee: ', number_format($feeCents / 100, 2), "\n";
