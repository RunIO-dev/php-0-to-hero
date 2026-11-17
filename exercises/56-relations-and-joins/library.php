<?php

declare(strict_types=1);

// The library the exercise tests use: books, members and loans, in a new database in memory.

function testLibrary(): PDO
{
    $db = new PDO('sqlite::memory:', options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec(<<<'SQL'
        PRAGMA foreign_keys = ON;
        CREATE TABLE books (id INTEGER PRIMARY KEY, title TEXT NOT NULL UNIQUE, author TEXT NOT NULL) STRICT;
        CREATE TABLE members (id INTEGER PRIMARY KEY, name TEXT NOT NULL UNIQUE) STRICT;
        CREATE TABLE loans (
            id INTEGER PRIMARY KEY,
            book_id INTEGER NOT NULL REFERENCES books (id),
            member_id INTEGER NOT NULL REFERENCES members (id),
            due TEXT NOT NULL
        ) STRICT;
        INSERT INTO books (title, author) VALUES
            ('Things Fall Apart', 'Chinua Achebe'), ('Kindred', 'Octavia E. Butler'), ('Kitchen', 'Banana Yoshimoto'),
            ('Sula', 'Toni Morrison'), ('Beloved', 'Toni Morrison');
        INSERT INTO members (name) VALUES ('Amira'), ('Jonas'), ('Kenji'), ('Ana');
        INSERT INTO loans (book_id, member_id, due) VALUES
            (2, 1, '2026-03-16'), (1, 1, '2026-03-20'), (2, 3, '2026-03-12'), (4, 2, '2026-03-30'), (5, 3, '2026-02-27');
        SQL);

    return $db;
}

/** @return list<array<string, mixed>> */
function rowsOf(PDO $db, string $sql): array
{
    $result = $db->query($sql);

    return $result === false ? [] : array_values($result->fetchAll());
}
