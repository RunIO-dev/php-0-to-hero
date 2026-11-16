<?php

$books = [
    ['title' => 'Things Fall Apart', 'copies' => 2],
    ['title' => 'Kindred', 'copies' => 0],
    ['title' => 'Kitchen', 'copies' => 1],
];

// Filter: keep only the books that pass a test.
$onShelf = [];
foreach ($books as $book) {
    if ($book['copies'] > 0) {
        $onShelf[] = $book;
    }
}

echo count($onShelf), " of ", count($books), " books are on the shelf\n";
