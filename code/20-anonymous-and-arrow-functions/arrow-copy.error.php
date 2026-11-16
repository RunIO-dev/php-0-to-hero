<?php

declare(strict_types=1);

$count = 0;

// An arrow function gets a copy of $count. Adding to the copy changes nothing outside.
$countBook = fn(): int => $count++;
$countBook();
$countBook();

echo "Counted: $count\n";
