<?php

declare(strict_types=1);

// Exercise 54, beginner: the titles by one author, in alphabetical order.

/** @return list<string> */
function titlesBy(PDO $db, string $author): array
{
    $find = $db->prepare('SELECT title FROM books WHERE author = :author ORDER BY title');
    $find->execute(['author' => $author]);

    return array_column($find->fetchAll(PDO::FETCH_ASSOC), 'title');
}
