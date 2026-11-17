<?php

declare(strict_types=1);

// A placeholder stands for a value, never for a name such as a column.

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

$wrong = $db->prepare('SELECT title FROM books ORDER BY :column');
$wrong->execute(['column' => 'year']);
echo 'ORDER BY :column: ', json_encode(array_column($wrong->fetchAll(), 'title')), "\n";

/** The SQL for one of the sorts we offer; anything else is refused. */
function sortedBy(string $sort): string
{
    return match ($sort) {
        'title' => 'SELECT title FROM books ORDER BY title',
        'oldest' => 'SELECT title FROM books ORDER BY year',
        'newest' => 'SELECT title FROM books ORDER BY year DESC',
        default => throw new InvalidArgumentException("Books can't be sorted by \"$sort\""),
    };
}

$oldest = $db->query(sortedBy('oldest'));
echo 'oldest: ', json_encode(array_column($oldest === false ? [] : $oldest->fetchAll(), 'title')), "\n";
try {
    sortedBy('year; DROP TABLE books');
} catch (InvalidArgumentException $e) {
    echo $e->getMessage(), "\n";
}
