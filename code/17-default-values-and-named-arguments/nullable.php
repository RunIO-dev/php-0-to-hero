<?php

declare(strict_types=1);

// ?string: a string, or null. Here null means "not on loan".
function dueText(?string $dueDate): string
{
    return $dueDate === null ? 'on the shelf' : "due back on $dueDate";
}

echo 'Kindred: ', dueText('2026-12-01'), "\n";
echo 'Kitchen: ', dueText(null), "\n";

/**
 * The author of a title, or null when the title is not in the list.
 *
 * @param array<string, string> $authors title => author
 */
function authorOf(array $authors, string $title): ?string
{
    return $authors[$title] ?? null;
}

$authors = ['Kindred' => 'Octavia E. Butler', 'Kitchen' => 'Banana Yoshimoto'];
echo authorOf($authors, 'Kitchen') ?? 'unknown', "\n";
echo authorOf($authors, 'Sula') ?? 'unknown', "\n";
