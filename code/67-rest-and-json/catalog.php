<?php

declare(strict_types=1);

// What this example's pages share: the books, as a database would hold them, and one way to answer with JSON.

const BOOKS = [
    1 => ['id' => 1, 'title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958],
    2 => ['id' => 2, 'title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979],
    3 => ['id' => 3, 'title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988],
];

/** Answers with $data as JSON, and ends the request. */
function json(int $status, mixed $data): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
    exit;
}
