<?php

declare(strict_types=1);

// The library the exercise tests use, as it is before your migration: tables with data to keep.

function testLibrary(): PDO
{
    $db = new PDO('sqlite::memory:', options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec(<<<'SQL'
        CREATE TABLE books (id INTEGER PRIMARY KEY, title TEXT NOT NULL, copies INTEGER NOT NULL);
        CREATE TABLE members (id INTEGER PRIMARY KEY, name TEXT NOT NULL UNIQUE) STRICT;
        CREATE TABLE loans (
            id INTEGER PRIMARY KEY,
            book_id INTEGER NOT NULL REFERENCES books (id),
            member_id INTEGER NOT NULL REFERENCES members (id),
            due TEXT NOT NULL
        ) STRICT;
        INSERT INTO books (id, title, copies) VALUES (1, 'Things Fall Apart', 2), (3, 'Kindred', 0), (4, 'Sula', 1);
        INSERT INTO members (name) VALUES ('Amira'), ('Jonas');
        INSERT INTO loans (book_id, member_id, due) VALUES (3, 1, '2026-03-16'), (1, 2, '2026-03-20');
        SQL);

    return $db;
}

/** Runs a migration file in one transaction, as Shelf's Migrations class does. */
function migrate(PDO $db, string $file): void
{
    $db->beginTransaction();
    $db->exec((string) file_get_contents($file));
    $db->commit();
}

/** @return list<array<string, mixed>> */
function rowsOf(PDO $db, string $sql): array
{
    $result = $db->query($sql);

    return $result === false ? [] : array_values($result->fetchAll());
}
