<?php

declare(strict_types=1);

// Exercise 39, beginner: tidy a messy file. A solution.

function lateFine(int $days, bool $isStaff = false): int
{
    if ($isStaff) {
        return 0;
    }
    $fine = $days * 20;

    return min($fine, 500);
}

echo lateFine(3), "\n", lateFine(40), "\n", lateFine(3, true), "\n";
