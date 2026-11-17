<?php

declare(strict_types=1);

// Exercise 56, beginner: questions that need two or three tables (see ../library.php).
//
// 'loans':  every loan as title, name and due, the soonest due first.
// 'idle':   the titles of the books that nobody has borrowed, in alphabetical order (one column: title).

return [
    'loans' => 'SELECT due FROM loans',
    'idle' => 'SELECT title FROM books',
];
// Check your work: composer exercise 56 beginner
