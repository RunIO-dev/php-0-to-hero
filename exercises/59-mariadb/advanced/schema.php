<?php

declare(strict_types=1);

// Exercise 59, advanced: one table, in the words of each database.
//
// createBooks($db) makes the table exercise_books, on SQLite or on MariaDB, with:
//   id:     a number the database chooses (1, 2, ...) when a row is added without one;
//   title:  text of up to 200 characters, required, unique without case: 'SULA' is 'Sula';
//   copies: a whole number, at least 0, 1 when an INSERT leaves it out.
// Text in any language must come back as it went in.
//
// The words differ: SQLite has INTEGER PRIMARY KEY, COLLATE NOCASE and STRICT;
// MariaDB has AUTO_INCREMENT, VARCHAR(200), and DEFAULT CHARSET = utf8mb4 with a case-insensitive
// COLLATE such as utf8mb4_unicode_ci. Both have CHECK and DEFAULT.

function createBooks(PDO $db): void
{
    $db->exec('CREATE TABLE exercise_books (id INTEGER, title TEXT, copies INTEGER)');
}
// Check your work: composer exercise 59 advanced
