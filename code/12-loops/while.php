<?php

$pagesLeft = 100;
$pagesPerDay = 30;
$day = 0;

while ($pagesLeft > 0) {
    $day++;
    $pagesLeft = max(0, $pagesLeft - $pagesPerDay);
    echo "Day $day: $pagesLeft pages left\n";
}

echo "Finished on day $day\n";
