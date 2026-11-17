<?php

declare(strict_types=1);

// ✗ The sort column comes straight from the address: ?sort=year
// A placeholder can't help here, because a column name isn't a value.

require_once __DIR__ . '/catalog.php';

$sort = is_string($_GET['sort'] ?? null) ? $_GET['sort'] : 'title';
$books = database()->query("SELECT title, author, year FROM books ORDER BY $sort")->fetchAll();
