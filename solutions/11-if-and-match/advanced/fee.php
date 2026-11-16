<?php

$daysLate = 4;
$feePerDayCents = 25;
$maxDays = 10;

$message = match (true) {
    $daysLate <= 0 => 'Returned on time',
    $daysLate <= $maxDays => 'Late fee: ' . number_format($daysLate * $feePerDayCents / 100, 2),
    default => 'Late fee: ' . number_format($maxDays * $feePerDayCents / 100, 2) . ' (the maximum)',
};

echo $message, "\n";
