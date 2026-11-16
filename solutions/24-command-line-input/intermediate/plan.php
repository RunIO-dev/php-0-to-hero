<?php

declare(strict_types=1);

$range = ['options' => ['min_range' => 1, 'max_range' => 10000]];
$pages = filter_var($argv[1] ?? '', FILTER_VALIDATE_INT, $range);
$perDay = filter_var($argv[2] ?? '', FILTER_VALIDATE_INT, $range);

if ($pages === false || $perDay === false) {
    fwrite(STDERR, "Pages and pages per day must be whole numbers from 1 to 10000.\n");
    exit(1);
}

$days = (int) ceil($pages / $perDay);
echo "$pages pages at $perDay a day: $days days\n";
