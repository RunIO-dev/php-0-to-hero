<?php

declare(strict_types=1);

// Exercise 54, advanced: the book list, sorted as the visitor chooses (?sort=newest).
//
// The sorts: 'title' (A to Z), 'newest' (newest year first), 'oldest' (oldest year first),
// and 'available' (most copies first, then by title). Each returns every title.
// Anything else throws an InvalidArgumentException, and nothing runs.
//
// A placeholder can't stand for a column or for DESC: choose the whole SQL from a fixed list.

/** @return list<string> */
function sortedTitles(PDO $db, string $sort): array
{
    $result = $db->query('SELECT title FROM books');

    return $result === false ? [] : array_column($result->fetchAll(PDO::FETCH_ASSOC), 'title');
}
// Check your work: composer exercise 54 advanced
