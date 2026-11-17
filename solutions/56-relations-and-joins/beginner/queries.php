<?php

declare(strict_types=1);

// Exercise 56, beginner: questions that need two or three tables (see ../library.php).

return [
    'loans' => 'SELECT books.title, members.name, loans.due
        FROM loans
        JOIN books ON books.id = loans.book_id
        JOIN members ON members.id = loans.member_id
        ORDER BY loans.due',
    'idle' => 'SELECT books.title FROM books LEFT JOIN loans ON loans.book_id = books.id
        WHERE loans.id IS NULL ORDER BY books.title',
];
