<?php

declare(strict_types=1);

// Exercise 53, intermediate: changes to the library, in SQL. The table is in ../library.sql.
//
// 'add':    a new book: Americanah by Chimamanda Ngozi Adichie, 2013, with 2 copies.
// 'return': Beloved comes back: one copy more of it, and of no other book.
// 'clear':  remove every book of which no copy is left. The other books stay.
//
// The test runs them in this order, then looks at the whole table.

return [
    'add' => 'SELECT 1',
    'return' => 'SELECT 1',
    'clear' => 'SELECT 1',
];
// Check your work: composer exercise 53 intermediate
