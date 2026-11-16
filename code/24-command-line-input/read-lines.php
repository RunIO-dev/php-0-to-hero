<?php

declare(strict_types=1);

// Read line after line until the input ends: fgets then returns false.
$count = 0;
while (($line = fgets(STDIN)) !== false) {
    $title = trim($line);
    if ($title !== '') {
        $count++;
        echo "$count. $title\n";
    }
}
