<?php

declare(strict_types=1);

// The JSON of chapter 23, as an API sends it: a list of objects, readable, with slashes and letters as they are.

$books = [
    ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988, 'cover' => '/cover?title=Kitchen'],
    ['title' => 'Cien años de soledad', 'author' => 'Gabriel García Márquez', 'year' => 1967, 'cover' => null],
];

echo json_encode($books, JSON_THROW_ON_ERROR), "\n\n";
echo json_encode($books, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
