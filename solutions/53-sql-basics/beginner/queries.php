<?php

declare(strict_types=1);

// Exercise 53, beginner: questions to the library, in SQL. The table is in ../library.sql.

return [
    'morrison' => "SELECT title FROM books WHERE author = 'Toni Morrison' ORDER BY title",
    'newest' => 'SELECT title, year FROM books ORDER BY year DESC LIMIT 3',
    'available' => 'SELECT title FROM books WHERE copies > 0 ORDER BY title',
];
