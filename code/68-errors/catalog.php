<?php

declare(strict_types=1);

// What this example's pages share: the books, an answer with JSON, and an answer with a problem.

const BOOKS = [
    1 => ['id' => 1, 'title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958],
    2 => ['id' => 2, 'title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979],
    3 => ['id' => 3, 'title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988],
];

const FLAGS = JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

function json(int $status, mixed $data): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, FLAGS), "\n";
    exit;
}

/**
 * Answers with problem details (RFC 9457): one shape for every error, that programs can read.
 * Without a $type of its own, the problem is just the status: type "about:blank", and its usual name as the title.
 *
 * @param array<string, mixed> $more more members, such as "errors"
 */
function problem(int $status, string $title, string $detail, string $type = 'about:blank', array $more = []): never
{
    http_response_code($status);
    header('Content-Type: application/problem+json');
    echo json_encode(['type' => $type, 'title' => $title, 'status' => $status, 'detail' => $detail, ...$more], FLAGS), "\n";
    exit;
}
