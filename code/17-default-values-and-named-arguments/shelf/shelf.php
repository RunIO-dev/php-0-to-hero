<?php

declare(strict_types=1);

// Shelf: a small lending library. Chapter 17: the search can also filter by author.

$books = [
    ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958, 'copies' => 2],
    ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979, 'copies' => 0],
    ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988, 'copies' => 1],
    ['title' => 'Parable of the Sower', 'author' => 'Octavia E. Butler', 'year' => 1993, 'copies' => 1],
];

/** @param array{title: string, author: string, year: int, copies: int} $book */
function statusOf(array $book): string
{
    return $book['copies'] > 0 ? 'available' : 'all copies on loan';
}

/** @param array{title: string, author: string, year: int, copies: int} $book */
function bookLine(array $book): string
{
    return "- {$book['title']} ({$book['author']}, {$book['year']}): " . statusOf($book);
}

/**
 * Every title that contains the search text, in any case, optionally only by one author.
 *
 * @param list<array{title: string, author: string, year: int, copies: int}> $books
 * @return list<string>
 */
function searchTitles(array $books, string $search, ?string $author = null): array
{
    $found = [];
    foreach ($books as $book) {
        if ($author !== null && !str_contains(mb_strtolower($book['author']), mb_strtolower($author))) {
            continue;
        }
        if (str_contains(mb_strtolower($book['title']), mb_strtolower($search))) {
            $found[] = $book['title'];
        }
    }

    return $found;
}

/** @param list<array{title: string, author: string, year: int, copies: int}> $books */
function searchLine(array $books, string $search, ?string $author = null): string
{
    $found = searchTitles($books, $search, $author);
    $by = $author === null ? '' : " by \"$author\"";

    return "Search \"$search\"$by: " . ($found === [] ? 'nothing found' : implode(', ', $found));
}

foreach ($books as $book) {
    echo bookLine($book), "\n";
}
echo count($books), " books\n\n";

echo searchLine($books, 'KI'), "\n";
echo searchLine($books, 'KI', author: 'butler'), "\n";
echo searchLine($books, '', author: 'butler'), "\n";
