<?php

declare(strict_types=1);

$dueOn = new DateTimeImmutable('2026-12-01');
$returnedOn = new DateTimeImmutable('2026-12-04');

// Dates compare with < and >, like numbers.
if ($returnedOn > $dueOn) {
    // diff() gives the time between two dates; ->days is the number of whole days.
    $daysLate = $dueOn->diff($returnedOn)->days;
    echo "Returned $daysLate days late\n";
}
