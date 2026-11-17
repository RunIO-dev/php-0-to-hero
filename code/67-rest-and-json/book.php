<?php

declare(strict_types=1);

// One book, found by its ID in the address: /book.php?id=2.

require_once __DIR__ . '/catalog.php';

$id = is_string($_GET['id'] ?? null) ? (int) $_GET['id'] : 0;
$book = BOOKS[$id] ?? null;
if ($book === null) {
    json(404, ['error' => 'There is no such book.']);
}
json(200, $book);
