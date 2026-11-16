<?php

$pages = 328;
$pagesPerDay = 25;

echo 'Days: ', ceil($pages / $pagesPerDay), "\n";
echo 'Last day: ', $pages % $pagesPerDay, " pages\n";
