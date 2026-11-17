<?php

declare(strict_types=1);

// A JOIN without ON pairs every row with every row.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'SELECT COUNT(*) FROM loans JOIN books');
show($db, 'SELECT COUNT(*) FROM loans JOIN books ON books.id = loans.book_id');
