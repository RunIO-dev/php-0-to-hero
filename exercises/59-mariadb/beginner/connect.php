<?php

declare(strict_types=1);

// Exercise 59, beginner: the application's connection to MariaDB.
//
// appConnection() connects to the database shelf on the host db, as the user shelf_app, with:
//   - the password from the environment variable SHELF_APP_PASSWORD (getenv), never written here;
//   - the character set utf8mb4, in the DSN;
//   - errors as exceptions, and real prepared statements (PDO::ATTR_EMULATE_PREPARES => false).
// If the variable isn't set, it throws a RuntimeException that says which one is missing.

function appConnection(): PDO
{
    return new PDO('sqlite::memory:');
}
// Check your work: composer exercise 59 beginner
