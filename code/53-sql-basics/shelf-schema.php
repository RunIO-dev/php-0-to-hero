<?php

declare(strict_types=1);

// Shelf's table, with its rules: the database refuses what breaks them.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/shelf/schema.sql');

show($db, "INSERT INTO books (title, author, year, copies) VALUES ('Kindred', 'Octavia E. Butler', 1979, 1)");
show($db, "INSERT INTO books (title, author, year) VALUES ('KINDRED', 'Octavia E. Butler', 1979)");
show($db, "INSERT INTO books (title, author, year, copies) VALUES ('Sula', 'Toni Morrison', 1973, -1)");
show($db, "INSERT INTO books (title, author, year) VALUES ('Sula', 'Toni Morrison', 'last year')");
show($db, "INSERT INTO books (title, year) VALUES ('Sula', 1973)");
show($db, 'SELECT * FROM books');
