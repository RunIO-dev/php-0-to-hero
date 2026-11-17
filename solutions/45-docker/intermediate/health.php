<?php

declare(strict_types=1);

// Exercise 45, intermediate: a health check. A solution.

header('Content-Type: application/json');

$file = __DIR__ . '/' . basename(getenv('BOOKS_FILE') ?: 'books.json');
$json = is_readable($file) ? file_get_contents($file) : false;
$books = $json === false ? null : json_decode($json, true);

if (!is_array($books) || !array_is_list($books)) {
    http_response_code(503);
    echo json_encode(['status' => 'down', 'reason' => $json === false ? 'the books file is missing' : 'the books file is not a list'], JSON_THROW_ON_ERROR), "\n";
    exit;
}

echo json_encode(['status' => 'ok', 'books' => count($books)], JSON_THROW_ON_ERROR), "\n";
