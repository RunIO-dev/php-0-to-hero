<?php

declare(strict_types=1);

// Shelf: a small lending library. Chapter 19: lending a book, with exceptions.

final class BookNotFound extends RuntimeException {}

final class NoCopyLeft extends RuntimeException {}

$books = [
    ['title' => 'Things Fall Apart', 'author' => 'Chinua Achebe', 'year' => 1958, 'copies' => 2],
    ['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979, 'copies' => 0],
    ['title' => 'Kitchen', 'author' => 'Banana Yoshimoto', 'year' => 1988, 'copies' => 1],
    ['title' => 'Parable of the Sower', 'author' => 'Octavia E. Butler', 'year' => 1993, 'copies' => 1],
];

/** @param array{title: string, author: string, year: int, copies: int} $book */
function statusOf(array $book): string
{
    return $book['copies'] > 0 ? 'available' : 'all copies on loan';
}

/** @param array{title: string, author: string, year: int, copies: int} $book */
function bookLine(array $book): string
{
    return "- {$book['title']} ({$book['author']}, {$book['year']}): " . statusOf($book);
}

/**
 * The position of the book with exactly this title, in any case.
 *
 * @param list<array{title: string, author: string, year: int, copies: int}> $books
 */
function findBook(array $books, string $title): int
{
    foreach ($books as $position => $book) {
        if (mb_strtolower($book['title']) === mb_strtolower($title)) {
            return $position;
        }
    }

    throw new BookNotFound("There is no book titled \"$title\"");
}

/**
 * The books after one copy of $title is lent.
 *
 * @param list<array{title: string, author: string, year: int, copies: int}> $books
 * @return list<array{title: string, author: string, year: int, copies: int}>
 */
function lend(array $books, string $title): array
{
    $position = findBook($books, $title);
    if ($books[$position]['copies'] === 0) {
        throw new NoCopyLeft("All copies of \"{$books[$position]['title']}\" are on loan");
    }
    $books[$position]['copies']--;

    return $books;
}

foreach (['Kitchen', 'kindred', 'Sula'] as $wanted) {
    try {
        $books = lend($books, $wanted);
        echo "Lent: $wanted\n";
    } catch (BookNotFound|NoCopyLeft $e) {
        echo 'Not lent: ', $e->getMessage(), "\n";
    }
}

echo "\n";
foreach ($books as $book) {
    echo bookLine($book), "\n";
}
