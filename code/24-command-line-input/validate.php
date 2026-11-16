<?php

declare(strict_types=1);

// Days late, as typed: never trust it. Is it a whole number from 0 to 365?
$range = ['options' => ['min_range' => 0, 'max_range' => 365]];
foreach (array_slice($argv, 1) as $input) {
    $days = filter_var($input, FILTER_VALIDATE_INT, $range);
    echo str_pad("\"$input\"", 8), $days === false ? 'refused' : "accepted: $days", "\n";
}
