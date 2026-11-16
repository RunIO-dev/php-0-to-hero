<?php

declare(strict_types=1);

$books = [
    ['title' => 'Things Fall Apart', 'year' => 1958, 'copies' => 2],
    ['title' => 'Kindred', 'year' => 1979, 'copies' => 0],
    ['title' => 'Kitchen', 'year' => 1988, 'copies' => 1],
];

$since = 1970;

// fn: one expression, returned automatically, and $since is seen without use.
$newer = array_filter($books, fn(array $book): bool => $book['year'] >= $since);
$titles = array_map(fn(array $book): string => $book['title'], $newer);

echo implode(', ', $titles), "\n";
