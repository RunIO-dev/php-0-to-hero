<?php

declare(strict_types=1);

// Shelf: a small lending library. Chapter 24: a command with list, search and lend.
// Run: php shelf/shelf.php [list | search <text> [<author>] | lend <title>]

final class BookNotFound extends RuntimeException {}

final class NoCopyLeft extends RuntimeException {}

const LOAN_DAYS = 14;


/**
 * The books in a JSON file: a list of objects with title, author, year and copies.
 *
 * @return list<array{title: string, author: string, year: int, copies: int}>
 */
function loadBooks(string $path): array
{
    $json = is_readable($path) ? file_get_contents($path) : false;
    if ($json === false) {
        throw new RuntimeException("Cannot read the books from $path");
    }
    $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($data) || !array_is_list($data)) {
        throw new UnexpectedValueException("$path must hold a list of books");
    }

    return array_map(fn(mixed $item): array => toBook($item), $data);
}

/**
 * One book from the file, checked: the file is input, like anything from outside.
 *
 * @return array{title: string, author: string, year: int, copies: int}
 */
function toBook(mixed $item): array
{
    if (!is_array($item) || !is_string($item['title'] ?? null) || !is_string($item['author'] ?? null)
        || !is_int($item['year'] ?? null) || !is_int($item['copies'] ?? null)) {
        throw new UnexpectedValueException('Each book needs a title, an author, a year and copies: ' . json_encode($item));
    }

    return ['title' => $item['title'], 'author' => $item['author'], 'year' => $item['year'], 'copies' => $item['copies']];
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

/** @param list<array{title: string, author: string, year: int, copies: int}> $books */
function saveBooks(string $path, array $books): void
{
    $json = json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    if (file_put_contents($path, $json . "\n") === false) {
        throw new RuntimeException("Cannot save the books to $path");
    }
}

/** @param list<array{title: string, author: string, year: int, copies: int}> $books */
function listText(array $books): string
{
    $lines = array_map(fn(array $book): string => bookLine($book) . "\n", sortedByTitle($books));

    return implode('', $lines) . count($books) . " books\n";
}

/** @param list<array{title: string, author: string, year: int, copies: int}> $books */
function lendAndSave(array $books, string $title, string $path, DateTimeImmutable $today): string
{
    saveBooks($path, lend($books, $title));

    return "Lent: $title, due back on " . dueDate($today)->format('l, j F Y') . "\n";
}

const USAGE = <<<TXT
    Usage: php shelf/shelf.php list
           php shelf/shelf.php search <text> [<author>]
           php shelf/shelf.php lend <title>
    TXT;

/**
 * Runs one command and returns the text to print.
 *
 * @param list<string> $args the words after the script's name
 */
function run(array $args, string $path, DateTimeImmutable $today): string
{
    $command = $args[0] ?? 'list';
    $text = trim($args[1] ?? '');
    if (!in_array($command, ['list', 'search', 'lend'], true)) {
        throw new InvalidArgumentException("Unknown command \"$command\"\n" . USAGE);
    }
    if ($command !== 'list' && $text === '') {
        throw new InvalidArgumentException(USAGE);
    }
    $books = loadBooks($path);

    return match ($command) {
        'list' => listText($books),
        'search' => searchLine($books, $text, $args[2] ?? null) . "\n",
        'lend' => lendAndSave($books, $text, $path, $today),
    };
}

try {
    echo run(array_slice($argv, 1), __DIR__ . '/books.json', new DateTimeImmutable('today'));
} catch (InvalidArgumentException|BookNotFound|NoCopyLeft $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
