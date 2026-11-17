<?php

declare(strict_types=1);

// One resource, the list of books: GET reads it, POST adds a book to it. Every answer is JSON.

require_once __DIR__ . '/catalog.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json(200, array_values(BOOKS));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: GET, POST');
    json(405, ['error' => 'Use GET or POST.']);
}

try {
    $sent = json_decode((string) file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException) {
    json(400, ['error' => 'The body must be JSON.']);
}
if (!is_array($sent) || !is_string($sent['title'] ?? null) || trim($sent['title']) === ''
    || !is_string($sent['author'] ?? null) || trim($sent['author']) === '' || !is_int($sent['year'] ?? null)) {
    json(422, ['error' => 'A book needs a title, an author and a year, as a number.']);
}

$book = ['id' => max(array_keys(BOOKS)) + 1, 'title' => trim($sent['title']), 'author' => trim($sent['author']), 'year' => $sent['year']];
// A real API saves the book here; Shelf does, below.
header("Location: /book.php?id={$book['id']}");
json(201, $book);
