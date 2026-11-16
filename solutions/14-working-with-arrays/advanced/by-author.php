<?php

$books = [
    ['title' => 'Sula', 'author' => 'Toni Morrison'],
    ['title' => 'Kindred', 'author' => 'Octavia E. Butler'],
    ['title' => 'Song of Solomon', 'author' => 'Toni Morrison'],
    ['title' => 'Beloved', 'author' => 'Toni Morrison'],
];
$author = 'Toni Morrison';

$titles = [];
foreach ($books as $book) {
    if ($book['author'] === $author) {
        $titles[] = $book['title'];
    }
}
sort($titles, SORT_STRING | SORT_FLAG_CASE);

echo "$author: ", $titles === [] ? 'none' : implode(', ', $titles), "\n";
