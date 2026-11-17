<?php

declare(strict_types=1);

// Two tables in one query can have columns with the same name.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'SELECT id, title FROM loans JOIN books ON books.id = loans.book_id');
show($db, 'SELECT loans.id, books.title FROM loans JOIN books ON books.id = loans.book_id');
