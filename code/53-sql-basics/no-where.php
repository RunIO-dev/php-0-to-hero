<?php

declare(strict_types=1);

// Meant: one copy of Sula less. Every book loses one.

require_once __DIR__ . '/db.php';

$db = database(__DIR__ . '/library.sql');

show($db, 'UPDATE books SET copies = copies - 1');
show($db, 'SELECT title, copies FROM books');
