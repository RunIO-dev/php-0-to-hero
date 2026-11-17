<?php

declare(strict_types=1);

// The library the exercise tests use: books, members and loans, in a new database in memory.

function testLibrary(): PDO
{
    $db = new PDO('sqlite::memory:', options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec(<<<'SQL'
        PRAGMA foreign_keys = ON;
        CREATE TABLE books (
            id INTEGER PRIMARY KEY,
            title TEXT NOT NULL UNIQUE,
            copies INTEGER NOT NULL CHECK (copies >= 0)
        ) STRICT;
        CREATE TABLE members (id INTEGER PRIMARY KEY, name TEXT NOT NULL UNIQUE) STRICT;
        CREATE TABLE loans (
            id INTEGER PRIMARY KEY,
            book_id INTEGER NOT NULL REFERENCES books (id),
            member_id INTEGER NOT NULL REFERENCES members (id),
            due TEXT NOT NULL
        ) STRICT;
        INSERT INTO books (title, copies) VALUES ('Kindred', 0), ('Sula', 2), ('Kitchen', 1), ('Beloved', 3);
        INSERT INTO members (name) VALUES ('Amira'), ('Jonas');
        INSERT INTO loans (book_id, member_id, due) VALUES (1, 1, '2026-03-16'), (2, 1, '2026-03-20'), (4, 1, '2026-03-21');
        SQL);

    return $db;
}

/** "Kindred 0, Sula 2, ...; loans 1, 2, 3": what the tests compare before and after. */
function stateOf(PDO $db): string
{
    $books = $db->query('SELECT title, copies FROM books ORDER BY id');
    $loans = $db->query('SELECT id FROM loans ORDER BY id');
    $titles = array_map(fn(array $row): string => "{$row['title']} {$row['copies']}", $books === false ? [] : $books->fetchAll());

    return implode(', ', $titles) . '; loans ' . implode(', ', $loans === false ? [] : $loans->fetchAll(PDO::FETCH_COLUMN));
}
