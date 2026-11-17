<?php

declare(strict_types=1);

// The library the exercise tests use: a new database in memory for each test, with books and members.

function testLibrary(): PDO
{
    $db = new PDO('sqlite::memory:', options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec(<<<'SQL'
        CREATE TABLE books (
            id INTEGER PRIMARY KEY,
            title TEXT NOT NULL UNIQUE,
            author TEXT NOT NULL,
            year INTEGER NOT NULL,
            copies INTEGER NOT NULL DEFAULT 1
        ) STRICT;
        INSERT INTO books (title, author, year, copies) VALUES
            ('Things Fall Apart', 'Chinua Achebe', 1958, 2),
            ('Kindred', 'Octavia E. Butler', 1979, 0),
            ('Sula', 'Toni Morrison', 1973, 3),
            ('Beloved', 'Toni Morrison', 1987, 1),
            ('At Swim-Two-Birds', 'Flann O''Brien', 1939, 1);
        CREATE TABLE members (
            id INTEGER PRIMARY KEY,
            name TEXT NOT NULL UNIQUE,
            role TEXT NOT NULL CHECK (role IN ('reader', 'staff'))
        ) STRICT;
        INSERT INTO members (name, role) VALUES ('Jonas', 'staff');
        SQL);

    return $db;
}
