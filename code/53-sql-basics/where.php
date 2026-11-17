<?php

declare(strict_types=1);

// WHERE keeps the rows for which the condition is true.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, "SELECT title FROM books WHERE author = 'Octavia E. Butler'");
show($db, 'SELECT title, year, copies FROM books WHERE year >= 1980 AND copies > 0');
show($db, "SELECT title FROM books WHERE title LIKE '%the%'");
show($db, 'SELECT title FROM books WHERE isbn IS NOT NULL');
