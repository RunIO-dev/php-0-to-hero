<?php

declare(strict_types=1);

// Exercise 44, beginner: a status code. A solution.

const BOOKS = ['kindred' => 'Kindred, Octavia E. Butler, 1979', 'sula' => 'Sula, Toni Morrison, 1973'];

header('Content-Type: text/plain; charset=UTF-8');

$key = strtolower($_GET['title'] ?? '');
if (!isset(BOOKS[$key])) {
    http_response_code(404);
    echo "No such book\n";
    exit;
}

echo BOOKS[$key], "\n";
