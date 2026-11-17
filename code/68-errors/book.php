<?php

declare(strict_types=1);

// One book: GET reads it, DELETE removes it. A book that doesn't exist is a problem, in the same shape as every other.

require_once __DIR__ . '/catalog.php';

$id = is_string($_GET['id'] ?? null) ? (int) $_GET['id'] : 0;
$book = BOOKS[$id] ?? null;
if ($book === null) {
    problem(404, 'Not Found', "There is no book with the ID $id.");
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json(200, $book);
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // A real API removes the book here. Nothing is left to send: 204 No Content.
    http_response_code(204);
    ini_set('default_mimetype', ''); // PHP's default Content-Type: there is no content
    exit;
}
header('Allow: GET, DELETE');
problem(405, 'Method Not Allowed', 'Use GET or DELETE.');
