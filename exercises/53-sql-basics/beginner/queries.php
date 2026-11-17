<?php

declare(strict_types=1);

// Exercise 53, beginner: questions to the library, in SQL. The table is in ../library.sql.
//
// 'morrison':  the titles by Toni Morrison, in alphabetical order (one column: title).
// 'newest':    the three newest books, newest first (two columns: title, year).
// 'available': the titles with at least one copy on the shelf, in alphabetical order (one column: title).

return [
    'morrison' => 'SELECT title FROM books',
    'newest' => 'SELECT title, year FROM books',
    'available' => 'SELECT title FROM books',
];
// Check your work: composer exercise 53 beginner
