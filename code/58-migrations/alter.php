<?php

declare(strict_types=1);

// Changing a table that holds data: the rows stay, and the change applies to each of them.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'ALTER TABLE loans ADD COLUMN lent TEXT');
show($db, "UPDATE loans SET lent = date(due, '-14 days')");
show($db, 'ALTER TABLE members RENAME COLUMN name TO full_name');
show($db, 'SELECT books.title, members.full_name, loans.lent, loans.due
    FROM loans JOIN books ON books.id = loans.book_id JOIN members ON members.id = loans.member_id');
