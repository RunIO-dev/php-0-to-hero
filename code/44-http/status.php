<?php

declare(strict_types=1);

// The status code says how it went: 200 found, 404 not found.

const BOOKS = ['kindred' => 'Kindred, Octavia E. Butler, 1979', 'sula' => 'Sula, Toni Morrison, 1973'];

header('Content-Type: text/plain; charset=UTF-8');

$key = strtolower($_GET['title'] ?? '');
if (!isset(BOOKS[$key])) {
    http_response_code(404);
    echo "There is no book with that title.\n";
    exit;
}

echo BOOKS[$key], "\n";
