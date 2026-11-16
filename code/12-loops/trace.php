<?php

// Kenji reads 10 minutes on day 1, 20 on day 2, and so on.
$totalMinutes = 0;

for ($day = 1; $day <= 4; $day++) {
    $minutes = $day * 10;
    $totalMinutes += $minutes;
    echo "day $day: $minutes minutes, total $totalMinutes\n";
}
