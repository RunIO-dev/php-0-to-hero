<?php

declare(strict_types=1);

// ✗ Every book in one answer: fine with seven, slow with seventy thousand, and a gift to anyone who
// wants to copy the whole catalogue, or keep the server busy.

require_once __DIR__ . '/catalog.php';

json(200, database()->query('SELECT title, author, year FROM books')->fetchAll());
