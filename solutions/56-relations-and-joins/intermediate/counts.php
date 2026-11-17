<?php

declare(strict_types=1);

// Exercise 56, intermediate: how many books each member has, for the librarian's overview.

return [
    'members' => 'SELECT members.name, COUNT(loans.id) AS loans
        FROM members LEFT JOIN loans ON loans.member_id = members.id
        GROUP BY members.id
        ORDER BY loans DESC, members.name',
    'authors' => 'SELECT books.author, COUNT(*) AS loans
        FROM loans JOIN books ON books.id = loans.book_id
        GROUP BY books.author
        ORDER BY loans DESC, books.author',
];
