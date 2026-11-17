<?php

declare(strict_types=1);

// A loan holds two numbers. JOIN brings the rows they point to.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'SELECT * FROM loans');
show($db, 'SELECT books.title, members.name, loans.due
    FROM loans
    JOIN books ON books.id = loans.book_id
    JOIN members ON members.id = loans.member_id
    ORDER BY loans.due');
