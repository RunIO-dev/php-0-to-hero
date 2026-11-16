<?php

declare(strict_types=1);

/** @param array<string, string> $authors */
function authorOf(array $authors, string $title): ?string
{
    return $authors[$title] ?? null;
}

$authors = ['Kindred' => 'Octavia E. Butler'];
echo strtoupper(authorOf($authors, 'Sula')), "\n";
