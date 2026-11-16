<?php

// Shelf: a small lending library. Chapter 13: a list of books, each one a map.

$books = [
    ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958, 'copies' => 2],
    ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979, 'copies' => 0],
    ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988, 'copies' => 1],
];

foreach ($books as $book) {
    $status = $book['copies'] > 0 ? 'available' : 'all copies on loan';

    echo "Title:  {$book['title']}\n";
    echo "Author: {$book['author']}\n";
    echo "Year:   {$book['year']}\n";
    echo "Copies: {$book['copies']}\n";
    echo "Status: $status\n";
    echo "\n";
}

echo count($books), " books\n";
