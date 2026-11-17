<?php

declare(strict_types=1);

// Exercise 61, advanced: log in, and keep hashes up to date.
//
// The table: members (name TEXT PRIMARY KEY, password_hash TEXT), where password_hash may be NULL.
//
// logIn($db, $name, $password) returns true when the member exists, has a password, and the typed
// password matches it. After a successful login, if password_needs_rehash says the stored hash was
// made with older settings, store a new hash of the password with PASSWORD_DEFAULT.
// Use prepared statements.

function logIn(PDO $db, string $name, string $password): bool
{
    return false;
}
// Check your work: composer exercise 61 advanced
