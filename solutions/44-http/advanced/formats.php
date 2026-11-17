<?php

declare(strict_types=1);

// Exercise 44, advanced: one answer, two formats. A solution.

const BOOKS = [['title' => 'Kindred', 'year' => 1979], ['title' => 'Sula', 'year' => 1973]];

$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
if (is_string($accept) && str_contains($accept, 'application/json')) {
    header('Content-Type: application/json');
    echo json_encode(BOOKS, JSON_THROW_ON_ERROR), "\n";
    exit;
}

header('Content-Type: text/plain; charset=UTF-8');
foreach (BOOKS as $book) {
    echo "{$book['title']} ({$book['year']})\n";
}
