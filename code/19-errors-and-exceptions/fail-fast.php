<?php

declare(strict_types=1);

// Check the input first, and stop at once if it can't be right.
function pagesPerDay(int $pages, int $days): int
{
    if ($pages < 1) {
        throw new InvalidArgumentException("A book has at least 1 page, not $pages");
    }
    if ($days < 1) {
        throw new InvalidArgumentException("Reading takes at least 1 day, not $days");
    }

    return (int) ceil($pages / $days);
}

echo pagesPerDay(320, 14), " pages a day\n";

try {
    echo pagesPerDay(320, 0), " pages a day\n";
} catch (InvalidArgumentException $e) {
    echo 'Not possible: ', $e->getMessage(), "\n";
}
