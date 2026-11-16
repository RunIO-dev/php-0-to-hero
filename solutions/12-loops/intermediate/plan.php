<?php

$pages = 60;
$pagesPerDay = 25;

$pagesLeft = $pages;
$day = 0;

while ($pagesLeft > 0) {
    $day++;
    $pagesToday = min($pagesPerDay, $pagesLeft);
    $pagesLeft -= $pagesToday;
    echo "Day $day: $pagesToday pages, $pagesLeft left\n";
}
