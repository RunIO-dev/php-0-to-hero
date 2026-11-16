<?php

declare(strict_types=1);

function bookLine(
    string $title,
    string $author,
    int $year = 0,
    bool $withYear = true,
): string {
    $line = "$title by $author";

    return $withYear && $year > 0 ? "$line ($year)" : $line;
}

// By position: the order must match the parameters.
echo bookLine('Kindred', 'Octavia E. Butler', 1979), "\n";

// By name: the name says which parameter gets the value.
echo bookLine('Kindred', 'Octavia E. Butler', 1979, withYear: false), "\n";
echo bookLine(author: 'Banana Yoshimoto', title: 'Kitchen'), "\n";
