<?php

$books = [
    ['title' => 'Kindred', 'year' => 1979, 'copies' => 2],
    ['title' => 'Beloved', 'year' => 1987, 'copies' => 0],
    ['title' => 'Kitchen', 'year' => 1988, 'copies' => 3],
];

echo $books[1]['title'], "\n";

foreach ($books as $book) {
    echo "{$book['title']} ({$book['year']}): {$book['copies']} copies\n";
}
