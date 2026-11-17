<?php

declare(strict_types=1);

// Helpers for this chapter's examples: a library in a database, and what is in it now.

function library(string $dsn = 'sqlite::memory:'): PDO
{
    $db = new PDO($dsn, options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $db->exec('PRAGMA foreign_keys = ON');

    return $db;
}

function state(PDO $db): string
{
    $books = $db->query('SELECT title, copies FROM books ORDER BY title');
    $loans = $db->query('SELECT COUNT(*) FROM loans');
    $lines = array_map(fn(array $row): string => "{$row['title']}: {$row['copies']}", $books === false ? [] : $books->fetchAll());

    return 'copies ' . implode(', ', $lines) . '; loans: ' . ($loans === false ? '?' : $loans->fetchColumn()) . "\n";
}
