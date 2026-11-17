<?php

declare(strict_types=1);

// Exercise 59, intermediate: one function, two databases.
//
// saveCopies($db, $title, $copies) stores how many copies the library has of a title: it adds
// the title if it isn't there, and changes its copies if it is. The table (see the test) has a
// UNIQUE title. Use one statement, so two requests can't add the same title twice.
//
// SQLite and MariaDB write this differently:
//   SQLite:  INSERT ... ON CONFLICT (title) DO UPDATE SET copies = excluded.copies
//   MariaDB: INSERT ... ON DUPLICATE KEY UPDATE copies = VALUE(copies)
// $db->getAttribute(PDO::ATTR_DRIVER_NAME) says which one you have: 'sqlite' or 'mysql'.
// Anything else: a LogicException.

function saveCopies(PDO $db, string $title, int $copies): void
{
    $save = $db->prepare('INSERT INTO exercise_copies (title, copies) VALUES (:title, :copies)');
    $save->execute(['title' => $title, 'copies' => $copies]);
}
// Check your work: composer exercise 59 intermediate
