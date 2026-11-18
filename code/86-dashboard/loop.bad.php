<?php

declare(strict_types=1);

// ✗ Counting in PHP, one query per book: with 2,000 books, 2,001 queries for one page. And every loan
// comes back to PHP only to be counted. One query with GROUP BY does it in the database.

/** @return array<string, int> */
function loansPerBook(PDO $db): array
{
    $counts = [];
    foreach ($db->query('SELECT id, title FROM books') as $book) {
        $loans = $db->prepare('SELECT * FROM loans WHERE book_id = ?');
        $loans->execute([$book['id']]);
        $counts[$book['title']] = count($loans->fetchAll());
    }

    return $counts;
}
