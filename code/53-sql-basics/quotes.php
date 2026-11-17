<?php

declare(strict_types=1);

// Text in SQL goes between single quotes. A quote inside the text is written twice.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, "INSERT INTO books (title, author, year) VALUES ('Midnight's Children', 'Salman Rushdie', 1981)");
show($db, "INSERT INTO books (title, author, year) VALUES ('Midnight''s Children', 'Salman Rushdie', 1981)");
show($db, "SELECT title FROM books WHERE author = 'Salman Rushdie'");
