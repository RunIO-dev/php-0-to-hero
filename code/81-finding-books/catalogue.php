<?php

declare(strict_types=1);

// The books for chapter 81's examples: a database in memory, with a few books by the same authors
// and from the same years, as a real catalogue has.

function catalogue(): PDO
{
    $db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec('CREATE TABLE books (id INTEGER PRIMARY KEY, title TEXT NOT NULL UNIQUE COLLATE NOCASE,
        author TEXT NOT NULL, year INTEGER NOT NULL, isbn TEXT UNIQUE)');
    $add = $db->prepare('INSERT INTO books (title, author, year, isbn) VALUES (?, ?, ?, ?)');
    foreach ([
        ['Kindred', 'Octavia E. Butler', 1979, '9780807083697'],
        ['Dawn', 'Octavia E. Butler', 1987, null],
        ['Beloved', 'Toni Morrison', 1987, null],
        ['Sula', 'Toni Morrison', 1973, null],
        ['Kitchen', 'Banana Yoshimoto', 1988, '9780802142443'],
        ['Things Fall Apart', 'Chinua Achebe', 1958, null],
        ['Norwegian Wood', 'Haruki Murakami', 1987, null],
    ] as $book) {
        $add->execute($book);
    }

    return $db;
}
