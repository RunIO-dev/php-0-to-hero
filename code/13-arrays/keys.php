<?php

$book = ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979];

foreach ($book as $field => $value) {
    echo "$field: $value\n";
}

$titles = ['Kindred', 'Beloved'];

foreach ($titles as $position => $title) {
    echo "$position: $title\n";
}
