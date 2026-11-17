<?php

declare(strict_types=1);

// The library's new books, for its phone app: answer with the list as JSON.
// Send the right Content-Type, make it readable, and keep slashes and letters as they are.

const NEW_BOOKS = [
    ['title' => 'Cien años de soledad', 'author' => 'Gabriel García Márquez', 'cover' => '/cover?title=Cien'],
    ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'cover' => null],
];

print_r(NEW_BOOKS);
