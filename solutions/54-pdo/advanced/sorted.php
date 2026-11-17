<?php

declare(strict_types=1);

// Exercise 54, advanced: the book list, sorted as the visitor chooses (?sort=newest).

/** @return list<string> */
function sortedTitles(PDO $db, string $sort): array
{
    $sql = match ($sort) {
        'title' => 'SELECT title FROM books ORDER BY title',
        'newest' => 'SELECT title FROM books ORDER BY year DESC',
        'oldest' => 'SELECT title FROM books ORDER BY year',
        'available' => 'SELECT title FROM books ORDER BY copies DESC, title',
        default => throw new InvalidArgumentException("Books can't be sorted by \"$sort\""),
    };
    $result = $db->query($sql);

    return $result === false ? [] : array_column($result->fetchAll(PDO::FETCH_ASSOC), 'title');
}
