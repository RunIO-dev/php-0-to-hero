<?php

declare(strict_types=1);

// Shelf's rules: finding, lending and adding books. No input, no output, no files.

final class BookNotFound extends RuntimeException {}

final class NoCopyLeft extends RuntimeException {}

const LOAN_DAYS = 14;

/** Two titles are the same in any case, and with spaces around them. */
function sameTitle(string $a, string $b): bool
{
    return mb_strtolower(trim($a)) === mb_strtolower(trim($b));
}

/** @param array{title: string, author: string, year: int, copies: int, isbn: ?string} $book */
function statusOf(array $book): string
{
    return $book['copies'] > 0 ? 'available' : 'all copies on loan';
}

/** @param array{title: string, author: string, year: int, copies: int, isbn: ?string} $book */
function bookLine(array $book): string
{
    return "- {$book['title']} ({$book['author']}, {$book['year']}): " . statusOf($book);
}

/**
 * @param list<array{title: string, author: string, year: int, copies: int, isbn: ?string}> $books
 * @return list<array{title: string, author: string, year: int, copies: int, isbn: ?string}>
 */
function sortedByTitle(array $books): array
{
    usort($books, fn(array $a, array $b): int => strcasecmp($a['title'], $b['title']));

    return $books;
}

/**
 * Every title that contains the search text, in any case, optionally only by one author.
 *
 * @param list<array{title: string, author: string, year: int, copies: int, isbn: ?string}> $books
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

/** @param list<array{title: string, author: string, year: int, copies: int, isbn: ?string}> $books */
function findBook(array $books, string $title): int
{
    foreach ($books as $position => $book) {
        if (sameTitle($book['title'], $title)) {
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
 * @param list<array{title: string, author: string, year: int, copies: int, isbn: ?string}> $books
 * @return list<array{title: string, author: string, year: int, copies: int, isbn: ?string}>
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

/** An ISBN without hyphens and spaces. */
function isbnDigits(string $input): string
{
    return str_replace(['-', ' '], '', $input);
}

/** An ISBN-13: the regex checks the shape, arithmetic the last digit. */
function isIsbn13(string $input): bool
{
    $digits = isbnDigits($input);
    if (preg_match('/^97[89]\d{10}$/D', $digits) !== 1) {
        return false;
    }
    $sum = 0;
    foreach (str_split(substr($digits, 0, 12)) as $i => $digit) {
        $sum += (int) $digit * ($i % 2 === 0 ? 1 : 3);
    }

    return (10 - $sum % 10) % 10 === (int) $digits[12];
}

/** Text as typed, trimmed, and with every run of spaces made one space. */
function tidy(string $text): string
{
    return (string) preg_replace('/\s+/u', ' ', trim($text));
}

/**
 * The books with one new book: every field is checked first.
 *
 * @param list<array{title: string, author: string, year: int, copies: int, isbn: ?string}> $books
 * @param list<string> $fields title, author, year and, optionally, the ISBN
 * @return list<array{title: string, author: string, year: int, copies: int, isbn: ?string}>
 */
function addBook(array $books, array $fields): array
{
    $title = tidy($fields[0] ?? '');
    $author = tidy($fields[1] ?? '');
    $year = filter_var($fields[2] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2100]]);
    $isbn = isset($fields[3]) ? isbnDigits($fields[3]) : null;
    if ($title === '' || $author === '' || $year === false) {
        throw new InvalidArgumentException('A book needs a title, an author and a year from 1 to 2100.');
    }
    if ($isbn !== null && !isIsbn13($isbn)) {
        throw new InvalidArgumentException("\"{$fields[3]}\" is not a valid ISBN-13");
    }
    if (array_filter($books, fn(array $book): bool => sameTitle($book['title'], $title)) !== []) {
        throw new InvalidArgumentException("\"$title\" is already on the shelf");
    }
    $books[] = ['title' => $title, 'author' => $author, 'year' => $year, 'copies' => 1, 'isbn' => $isbn];

    return $books;
}
