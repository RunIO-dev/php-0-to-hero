<?php

declare(strict_types=1);

// Shelf: a small lending library. Chapter 22: the books are read from books.csv.

final class BookNotFound extends RuntimeException {}

final class NoCopyLeft extends RuntimeException {}

const LOAN_DAYS = 14;


/**
 * The books in a CSV file with the columns title, author, year and copies.
 *
 * @return list<array{title: string, author: string, year: int, copies: int}>
 */
function loadBooks(string $path): array
{
    $file = is_readable($path) ? fopen($path, 'r') : false;
    if ($file === false) {
        throw new RuntimeException("Cannot read the books from $path");
    }
    try {
        $books = [];
        fgetcsv($file, escape: ''); // the header line
        while (($row = fgetcsv($file, escape: '')) !== false) {
            [$title, $author, $year, $copies] = $row;
            $books[] = [
                'title' => (string) $title,
                'author' => (string) $author,
                'year' => (int) $year,
                'copies' => (int) $copies,
            ];
        }

        return $books;
    } finally {
        fclose($file);
    }
}

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
 * @param list<array{title: string, author: string, year: int, copies: int}> $books
 * @return list<array{title: string, author: string, year: int, copies: int}>
 */
function sortedByTitle(array $books): array
{
    usort($books, fn(array $a, array $b): int => strcasecmp($a['title'], $b['title']));

    return $books;
}

/**
 * Every title that contains the search text, in any case, optionally only by one author.
 *
 * @param list<array{title: string, author: string, year: int, copies: int}> $books
 * @return list<string>
 */
function searchTitles(array $books, string $search, ?string $author = null): array
{
    $contains = fn(string $text, string $part): bool
        => str_contains(mb_strtolower($text), mb_strtolower($part));
    $found = array_filter(
        $books,
        fn(array $book): bool => $contains($book['title'], $search)
            && ($author === null || $contains($book['author'], $author)),
    );

    return array_column($found, 'title');
}

/** @param list<array{title: string, author: string, year: int, copies: int}> $books */
function searchLine(array $books, string $search, ?string $author = null): string
{
    $found = searchTitles($books, $search, $author);
    $by = $author === null ? '' : " by \"$author\"";

    return "Search \"$search\"$by: " . ($found === [] ? 'nothing found' : implode(', ', $found));
}

/** @param list<array{title: string, author: string, year: int, copies: int}> $books */
function findBook(array $books, string $title): int
{
    foreach ($books as $position => $book) {
        if (mb_strtolower($book['title']) === mb_strtolower($title)) {
            return $position;
        }
    }

    throw new BookNotFound("There is no book titled \"$title\"");
}

function dueDate(DateTimeImmutable $lentOn): DateTimeImmutable
{
    return $lentOn->modify('+' . LOAN_DAYS . ' days');
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

$books = loadBooks(__DIR__ . '/books.csv');

// A fixed day, so the output is always the same. A real library uses 'today'.
$today = new DateTimeImmutable('2026-11-17');

foreach (['Kitchen', 'kindred', 'Sula'] as $wanted) {
    try {
        $books = lend($books, $wanted);
        echo "Lent: $wanted, due back on ", dueDate($today)->format('l, j F Y'), "\n";
    } catch (BookNotFound|NoCopyLeft $e) {
        echo 'Not lent: ', $e->getMessage(), "\n";
    }
}

echo "\n";
foreach (sortedByTitle($books) as $book) {
    echo bookLine($book), "\n";
}
echo "\n", searchLine($books, 'KI'), "\n";
echo searchLine($books, '', author: 'butler'), "\n";
