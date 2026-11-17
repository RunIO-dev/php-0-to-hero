<?php

declare(strict_types=1);

// Adding a book: staff only. No CSRF token: a browser never sends the Authorization header on its own.

require_once __DIR__ . '/account.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    problem(405, 'Method Not Allowed', 'Use POST.');
}
allow('staff');

try {
    $sent = json_decode((string) file_get_contents('php://input'), true, flags: JSON_THROW_ON_ERROR);
} catch (JsonException) {
    problem(400, 'Bad Request', 'The body is not valid JSON.');
}
if (!is_array($sent) || !is_string($sent['title'] ?? null) || trim($sent['title']) === '') {
    problem(422, 'Unprocessable Content', 'A book needs a title.');
}
// A real API saves the book here.
json(201, ['title' => trim($sent['title'])]);
