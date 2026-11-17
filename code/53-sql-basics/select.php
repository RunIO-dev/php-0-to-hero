<?php

declare(strict_types=1);

// SELECT: which columns, from which table, in which order, and how many rows.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'SELECT title, year FROM books');
show($db, 'SELECT title, year FROM books ORDER BY year DESC');
show($db, 'SELECT title, author FROM books ORDER BY title LIMIT 3');
show($db, 'SELECT * FROM books WHERE id = 2');
