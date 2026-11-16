<?php

declare(strict_types=1);

$books = [
    ['title' => 'Things Fall Apart', 'year' => 1958, 'copies' => 2],
    ['title' => 'Kindred', 'year' => 1979, 'copies' => 0],
    ['title' => 'Kitchen', 'year' => 1988, 'copies' => 1],
];

$available = array_filter($books, fn(array $book): bool => $book['copies'] > 0);
echo count($available), " available\n";
echo $available[0]['title'], "\n";
echo $available[1]['title'], "\n";
