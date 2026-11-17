<?php

declare(strict_types=1);

// Exercise 53, intermediate: changes to the library, in SQL. The table is in ../library.sql.

return [
    'add' => "INSERT INTO books (title, author, year, copies) VALUES ('Americanah', 'Chimamanda Ngozi Adichie', 2013, 2)",
    'return' => "UPDATE books SET copies = copies + 1 WHERE title = 'Beloved'",
    'clear' => 'DELETE FROM books WHERE copies = 0',
];
