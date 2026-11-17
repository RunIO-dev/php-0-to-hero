<?php

declare(strict_types=1);

// REFERENCES: a loan must point to a book and a member that exist, and keeps them from going.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, "INSERT INTO loans (book_id, member_id, due) VALUES (99, 1, '2026-04-01')");
show($db, "DELETE FROM books WHERE title = 'Kindred'");
show($db, "DELETE FROM books WHERE title = 'Kitchen'");
show($db, 'SELECT title FROM books');
