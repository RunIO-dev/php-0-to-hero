<?php

declare(strict_types=1);

// LEFT JOIN keeps every row on the left, even when nothing on the right matches.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'SELECT members.name, loans.due FROM members LEFT JOIN loans ON loans.member_id = members.id ORDER BY members.name');
show($db, 'SELECT members.name, COUNT(loans.id) AS loans
    FROM members LEFT JOIN loans ON loans.member_id = members.id
    GROUP BY members.id ORDER BY loans DESC, members.name');
show($db, 'SELECT books.title FROM books LEFT JOIN loans ON loans.book_id = books.id WHERE loans.id IS NULL');
