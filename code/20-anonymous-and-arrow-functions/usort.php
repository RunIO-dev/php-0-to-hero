<?php

declare(strict_types=1);

$books = [
    ['title' => 'Things Fall Apart', 'year' => 1958, 'copies' => 2],
    ['title' => 'Kindred', 'year' => 1979, 'copies' => 0],
    ['title' => 'Kitchen', 'year' => 1988, 'copies' => 1],
];

// usort sorts with your function: it compares two items, $a and $b.
// <=> gives -1, 0 or 1: is $a smaller than, equal to, or greater than $b?
usort($books, fn(array $a, array $b): int => $b['year'] <=> $a['year']);
echo implode(', ', array_column($books, 'year')), "\n";

usort($books, fn(array $a, array $b): int => strcasecmp($a['title'], $b['title']));
echo implode(', ', array_column($books, 'title')), "\n";
