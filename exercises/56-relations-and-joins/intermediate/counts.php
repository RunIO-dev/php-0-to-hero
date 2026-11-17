<?php

declare(strict_types=1);

// Exercise 56, intermediate: how many books each member has, for the librarian's overview.
//
// 'members': every member, with the number of loans as "loans", including members with none:
//            the most loans first, then by name.
// 'authors': every author with at least one book on loan, with the number of loans as "loans":
//            the most first, then by author.

return [
    'members' => 'SELECT name FROM members',
    'authors' => 'SELECT author FROM books',
];
// Check your work: composer exercise 56 intermediate
