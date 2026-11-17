<?php

declare(strict_types=1);

// findTitle can return null, and strtoupper needs a string.

/** @param list<string> $titles */
function findTitle(array $titles, string $search): ?string
{
    foreach ($titles as $title) {
        if (str_contains($title, $search)) {
            return $title;
        }
    }

    return null;
}

echo strtoupper(findTitle(['Kindred', 'Sula'], 'Kin')), "\n";
