<?php

declare(strict_types=1);

// What this example's pages share: a database with seven books, and the answers of chapter 68.

function database(): PDO
{
    $db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec('CREATE TABLE books (title TEXT NOT NULL, author TEXT NOT NULL, year INTEGER NOT NULL) STRICT');
    $add = $db->prepare('INSERT INTO books (title, author, year) VALUES (?, ?, ?)');
    foreach ([
        ['Things Fall Apart', 'Chinua Achebe', 1958], ['Kindred', 'Octavia E. Butler', 1979],
        ['Kitchen', 'Banana Yoshimoto', 1988], ['Parable of the Sower', 'Octavia E. Butler', 1993],
        ['Sula', 'Toni Morrison', 1973], ['Beloved', 'Toni Morrison', 1987], ['Dawn', 'Octavia E. Butler', 1987],
    ] as $book) {
        $add->execute($book);
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
