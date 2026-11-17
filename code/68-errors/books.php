<?php

declare(strict_types=1);

// Adding a book: every way a request can be wrong gets its own status, and the details a program needs.

require_once __DIR__ . '/catalog.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    problem(405, 'Method Not Allowed', 'Use POST.');
}
$type = is_string($_SERVER['CONTENT_TYPE'] ?? null) ? $_SERVER['CONTENT_TYPE'] : '';
if (!str_starts_with($type, 'application/json')) {
    problem(415, 'Unsupported Media Type', 'Send the book as application/json.');
}
try {
    $sent = json_decode((string) file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException) {
    problem(400, 'Bad Request', 'The body is not valid JSON.');
}

// Every mistake at once, so that a program can show them all to its user.
$errors = [];
if (!is_string($sent['title'] ?? null) || trim($sent['title']) === '') {
    $errors[] = ['field' => 'title', 'detail' => 'A title is needed, as text.'];
}
if (!is_string($sent['author'] ?? null) || trim($sent['author']) === '') {
    $errors[] = ['field' => 'author', 'detail' => 'An author is needed, as text.'];
}
if (!is_int($sent['year'] ?? null)) {
    $errors[] = ['field' => 'year', 'detail' => 'The year must be a number, such as 1988.'];
}
if ($errors !== []) {
    $wrong = count($errors) === 1 ? 'One field is' : count($errors) . ' fields are';
    problem(422, 'The book is not valid', "$wrong wrong.", '/problems/invalid-book', ['errors' => $errors]);
}
if (in_array(mb_strtolower(trim($sent['title'])), array_map(mb_strtolower(...), array_column(BOOKS, 'title')), true)) {
    problem(409, 'The book is already there', "\"{$sent['title']}\" is already on the shelf.", '/problems/duplicate-book');
}

header('Location: /book.php?id=4');
json(201, ['id' => 4, 'title' => trim($sent['title']), 'author' => trim($sent['author']), 'year' => $sent['year']]);
