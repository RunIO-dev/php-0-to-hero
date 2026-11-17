<?php

declare(strict_types=1);

// Exercise 54, intermediate: add a book, and return the id the database gave it.
//
// The values come from a form: use placeholders. lastInsertId() returns the new id as text;
// return it as an int. A title that is already in the table makes the database throw a
// PDOException (UNIQUE): let it go up, don't hide it.

function addBook(PDO $db, string $title, string $author, int $year, int $copies = 1): int
{
    return 0;
}
// Check your work: composer exercise 54 intermediate
