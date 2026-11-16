<?php

declare(strict_types=1);

$books = [
    ['title' => 'Things Fall Apart', 'year' => 1958, 'copies' => 2],
    ['title' => 'Kindred', 'year' => 1979, 'copies' => 0],
    ['title' => 'Kitchen', 'year' => 1988, 'copies' => 1],
];

// array_filter keeps the items for which the function returns true.
$available = array_filter($books, function (array $book): bool {
    return $book['copies'] > 0;
});

// array_map turns every item into what the function returns.
$titles = array_map(function (array $book): string {
    return $book['title'];
}, $available);

echo implode(', ', $titles), "\n";
