<?php

declare(strict_types=1);

// The orders a list can have (chapter 81), as a backed enum: the value is what the address says,
// ?sort=newest, and tryFrom() refuses anything else. Each order ends with the title, so books of the
// same year always come in one order, and a page never repeats a book or skips one.

require_once __DIR__ . '/catalogue.php';

enum BookSort: string
{
    case Title = 'title';
    case Newest = 'newest';

    /** ORDER BY, written here, never taken from the address (chapter 70). */
    public function sql(): string
    {
        return match ($this) {
            self::Title => 'title',
            self::Newest => 'year DESC, title',
        };
    }
}

$db = catalogue();
foreach (['newest', 'price', ''] as $asked) {
    $sort = BookSort::tryFrom($asked);
    echo "?sort=$asked: ", $sort === null ? "not an order, a 400\n" : "ORDER BY {$sort->sql()}\n";
}

// Three books from 1987: with the title as the last key, pages of two always split them the same way.
$page = $db->prepare('SELECT title, year FROM books ORDER BY ' . BookSort::Newest->sql() . ' LIMIT 2 OFFSET :offset');
foreach ([1, 2] as $number) {
    $page->execute(['offset' => ($number - 1) * 2]);
    echo "page $number: ", implode(', ', array_map(fn(array $book): string => "{$book['title']} ({$book['year']})", $page->fetchAll())), "\n";
}

// The pager's links keep the search and leave out what is the default anyway.
$search = array_filter(['q' => 'butler', 'available' => '', 'sort' => BookSort::Newest->value]);
echo '?' . http_build_query($search + ['page' => 2]), "\n";
