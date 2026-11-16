<?php

// Shelf: a small lending library. Chapter 14: the books, and a search by title.

$books = [
    ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958, 'copies' => 2],
    ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979, 'copies' => 0],
    ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988, 'copies' => 1],
];

foreach ($books as $book) {
    $status = $book['copies'] > 0 ? 'available' : 'all copies on loan';
    echo "- {$book['title']} ({$book['author']}, {$book['year']}): $status\n";
}
echo count($books), " books\n\n";

// Search: every book whose title contains the search text, in any case.
$search = 'KI';
$found = [];
foreach ($books as $book) {
    if (str_contains(mb_strtolower($book['title']), mb_strtolower($search))) {
        $found[] = $book['title'];
    }
}

echo "Search \"$search\": ", $found === [] ? 'nothing found' : implode(', ', $found), "\n";
