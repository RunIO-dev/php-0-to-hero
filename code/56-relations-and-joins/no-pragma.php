<?php

declare(strict_types=1);

// Without PRAGMA foreign_keys = ON, SQLite reads REFERENCES and ignores it.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');
$db->exec('PRAGMA foreign_keys = OFF');

show($db, "DELETE FROM books WHERE title = 'Kindred'");
show($db, 'SELECT loans.id, books.title, loans.due FROM loans LEFT JOIN books ON books.id = loans.book_id');
