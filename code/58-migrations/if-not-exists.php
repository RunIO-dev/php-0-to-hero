<?php

declare(strict_types=1);

// The table got a new column in the .sql file. The database that already has the table doesn't.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY, title TEXT NOT NULL UNIQUE, author TEXT NOT NULL, language TEXT
) STRICT');
show($db, 'SELECT title, language FROM books');
