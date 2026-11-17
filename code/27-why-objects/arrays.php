<?php

declare(strict_types=1);

// A book as an array: nothing says which keys it must have, or what they hold.
$book = ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979, 'copies' => 2];
$copy = ['title' => 'Kitchen', 'autor' => 'Banana Yoshimoto', 'year' => '1988'];

/** @param array<string, mixed> $book */
function describe(array $book): string
{
    return "{$book['title']} by " . ($book['author'] ?? '(no author)') . ', ' . gettype($book['year']);
}

echo describe($book), "\n";
echo describe($copy), "\n";
