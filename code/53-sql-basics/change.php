<?php

declare(strict_types=1);

// INSERT adds rows, UPDATE changes them, DELETE removes them. WHERE says which.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, "INSERT INTO books (title, author, year) VALUES ('Americanah', 'Chimamanda Ngozi Adichie', 2013)");
show($db, "UPDATE books SET copies = copies - 1 WHERE title = 'Sula'");
show($db, "DELETE FROM books WHERE title = 'Beloved'");
show($db, 'SELECT id, title, copies FROM books ORDER BY title');
