<?php

$books = [
    ['title' => 'Things Fall Apart', 'copies' => 2],
    ['title' => 'Kindred', 'copies' => 0],
    ['title' => 'Kitchen', 'copies' => 1],
];

// Map: turn every item into something else.
$labels = [];
foreach ($books as $book) {
    $labels[] = mb_strtoupper($book['title']);
}
echo implode(' | ', $labels), "\n";

// One column of a list of maps.
$titles = array_column($books, 'title');
echo implode(', ', $titles), "\n";
echo array_sum(array_column($books, 'copies')), " copies\n";
