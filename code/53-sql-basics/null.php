<?php

declare(strict_types=1);

// NULL means "no value": nothing is equal to it, not even NULL.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'SELECT title FROM books WHERE isbn = NULL');
show($db, 'SELECT title FROM books WHERE isbn IS NULL');
