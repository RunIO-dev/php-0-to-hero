<?php

declare(strict_types=1);

// The library's members, for its desk app: GET /members.php, as JSON, a page at a time.
// - ?role=reader or ?role=staff: only those members. Any other role: 400.
// - ?sort=name (the default) or ?sort=joined (the newest first); then by name. Any other sort: 400.
// - ?page= (from 1, the default 1) and ?per_page= (1 to 50, the default 10). Anything else: 400.
// - The answer: a list of {"name": ..., "role": ..., "joined": ...}, and the header
//   X-Total-Count with how many members match the filter.
// - Every 400 is problem details: type "about:blank", title "Bad Request", any detail.
// Never put text from the address into the SQL: placeholders for values, and a list of allowed sorts.

function database(): PDO
{
    $db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec('CREATE TABLE members (name TEXT NOT NULL, role TEXT NOT NULL, joined TEXT NOT NULL) STRICT');
    $add = $db->prepare('INSERT INTO members (name, role, joined) VALUES (?, ?, ?)');
    foreach ([
        ['Amira', 'reader', '2025-09-01'], ['Jonas', 'staff', '2023-02-14'], ['Kenji', 'reader', '2026-01-20'],
        ['Ana', 'reader', '2024-05-30'], ['Femi', 'staff', '2025-11-03'], ['Lena', 'reader', '2026-02-11'],
    ] as $member) {
        $add->execute($member);
    }

    return $db;
}

const FLAGS = JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

function json(int $status, mixed $data): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, FLAGS), "\n";
    exit;
}

function problem(int $status, string $title, string $detail): never
{
    http_response_code($status);
    header('Content-Type: application/problem+json');
    echo json_encode(['type' => 'about:blank', 'title' => $title, 'status' => $status, 'detail' => $detail], FLAGS), "\n";
    exit;
}

json(200, database()->query('SELECT name, role, joined FROM members')?->fetchAll());
