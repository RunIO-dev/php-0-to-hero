<?php

declare(strict_types=1);

$books = [
    ['title' => 'Things Fall Apart', 'year' => 1958, 'copies' => 2],
    ['title' => 'Kindred', 'year' => 1979, 'copies' => 0],
    ['title' => 'Kitchen', 'year' => 1988, 'copies' => 1],
];

$since = 1970;

// use ($since) hands the outside value to the function: a closure.
$newer = array_filter($books, function (array $book) use ($since): bool {
    return $book['year'] >= $since;
});

echo count($newer), " books since $since\n";
