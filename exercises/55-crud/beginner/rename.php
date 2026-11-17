<?php

declare(strict_types=1);

// Exercise 55, beginner: correct a book's title.
//
// renameBook() changes the title $from to $to, and returns true. If there is no book titled
// $from, nothing changes, and it returns false: rowCount() tells you. A title that another book
// has already makes the database throw a PDOException (UNIQUE); let it go up.

function renameBook(PDO $db, string $from, string $to): bool
{
    return true;
}
// Check your work: composer exercise 55 beginner
