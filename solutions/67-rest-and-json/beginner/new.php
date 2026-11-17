<?php

declare(strict_types=1);

// The library's new books, for its phone app: answer with the list as JSON.
// Send the right Content-Type, make it readable, and keep slashes and letters as they are.

const NEW_BOOKS = [
    ['title' => 'Cien años de soledad', 'author' => 'Gabriel García Márquez', 'cover' => '/cover?title=Cien'],
    ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'cover' => null],
];

header('Content-Type: application/json');
echo json_encode(NEW_BOOKS, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
