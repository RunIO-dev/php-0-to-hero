<?php

declare(strict_types=1);

function pagesPerDay(int $pages, int $days): int
{
    if ($days < 1) {
        throw new InvalidArgumentException("Reading takes at least 1 day, not $days");
    }

    return (int) ceil($pages / $days);
}
