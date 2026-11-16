<?php

$book = [
    'title' => 'Kindred',
    'author' => 'Octavia E. Butler',
    'year' => 1979,
];

echo $book['title'], "\n";

$book['copies'] = 2;
$book['copies']--;

echo "{$book['title']}: {$book['copies']} on the shelf\n";
