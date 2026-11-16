<?php

declare(strict_types=1);

// file() reads a file into a list of lines.
$titles = file(__DIR__ . '/titles.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($titles as $number => $title) {
    echo $number + 1, ". $title\n";
}
