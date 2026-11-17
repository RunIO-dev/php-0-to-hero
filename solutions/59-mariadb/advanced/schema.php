<?php

declare(strict_types=1);

// Exercise 59, advanced: one table, in the words of each database.

function createBooks(PDO $db): void
{
    $db->exec(match ($db->getAttribute(PDO::ATTR_DRIVER_NAME)) {
        'sqlite' => 'CREATE TABLE exercise_books (
            id INTEGER PRIMARY KEY,
            title TEXT NOT NULL UNIQUE COLLATE NOCASE CHECK (length(title) <= 200),
            copies INTEGER NOT NULL DEFAULT 1 CHECK (copies >= 0)
        ) STRICT',
        'mysql' => 'CREATE TABLE exercise_books (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL UNIQUE,
            copies INT NOT NULL DEFAULT 1 CHECK (copies >= 0)
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci',
        default => throw new LogicException('createBooks() knows SQLite and MariaDB only'),
    });
}
