<?php

$books = [
    ['title' => 'Things Fall Apart', 'copies' => 2],
    ['title' => 'Kindred', 'copies' => 0],
    ['title' => 'Kitchen', 'copies' => 1],
];

$total = 0;
foreach ($books as $book) {
    if ($book['copies'] === 0) {
        continue;
    }
    echo "- {$book['title']} ({$book['copies']})\n";
    $total += $book['copies'];
}

echo "Total: $total copies\n";
