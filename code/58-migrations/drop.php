<?php

declare(strict_types=1);

// Dropping a column, or a table, takes its data with it. There is no undo.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'ALTER TABLE books DROP COLUMN author');
show($db, 'SELECT * FROM books');
