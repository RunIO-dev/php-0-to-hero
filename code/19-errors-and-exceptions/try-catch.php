<?php

declare(strict_types=1);

/** @param list<string> $titles */
function positionOf(array $titles, string $title): int
{
    $position = array_search($title, $titles, true);
    if ($position === false) {
        throw new RuntimeException("No book titled \"$title\"");
    }

    return $position;
}

$titles = ['Things Fall Apart', 'Kindred', 'Kitchen'];

try {
    echo positionOf($titles, 'Kitchen'), "\n";
    echo positionOf($titles, 'Sula'), "\n";
    echo "Not printed: the line above threw.\n";
} catch (RuntimeException $e) {
    echo 'Sorry: ', $e->getMessage(), "\n";
}

echo "The program goes on.\n";
