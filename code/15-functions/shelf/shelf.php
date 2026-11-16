<?php

// Shelf: a small lending library. Chapter 15: the work is done by functions.

$books = [
    ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958, 'copies' => 2],
    ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979, 'copies' => 0],
    ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988, 'copies' => 1],
];

function statusOf($book)
{
    return $book['copies'] > 0 ? 'available' : 'all copies on loan';
}

function bookLine($book)
{
    return "- {$book['title']} ({$book['author']}, {$book['year']}): " . statusOf($book);
}

// Every title that contains the search text, in any case.
function searchTitles($books, $search)
{
    $found = [];
    foreach ($books as $book) {
        if (str_contains(mb_strtolower($book['title']), mb_strtolower($search))) {
            $found[] = $book['title'];
        }
    }

    return $found;
}

function searchLine($books, $search)
{
    $found = searchTitles($books, $search);

    return "Search \"$search\": " . ($found === [] ? 'nothing found' : implode(', ', $found));
}

foreach ($books as $book) {
    echo bookLine($book), "\n";
}
echo count($books), " books\n\n";

echo searchLine($books, 'KI'), "\n";
echo searchLine($books, 'apart'), "\n";
echo searchLine($books, 'Sula'), "\n";
