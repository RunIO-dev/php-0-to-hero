<?php

declare(strict_types=1);

// Questions about many rows at once: how many, how much, and per what.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'SELECT COUNT(*) AS books, SUM(copies) AS copies FROM books');
show($db, 'SELECT author, COUNT(*) AS books FROM books GROUP BY author ORDER BY books DESC, author');
