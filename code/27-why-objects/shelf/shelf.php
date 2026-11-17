<?php

declare(strict_types=1);

// Shelf: a small lending library. Chapter 27: a book is an object of the class Book.
// Run: php shelf/shelf.php [list | search <text> [<author>] | lend <title> | add <title> <author> <year> [<isbn>]]

require_once __DIR__ . '/books.php';
require_once __DIR__ . '/library.php';

const USAGE = <<<TXT
    Usage: php shelf/shelf.php list
           php shelf/shelf.php search <text> [<author>]
           php shelf/shelf.php lend <title>
           php shelf/shelf.php add <title> <author> <year> [<isbn>]
    TXT;

/**
 * The argument at $position, which the command can't do without.
 *
 * @param list<string> $args
 */
function required(array $args, int $position): string
{
    $value = trim($args[$position] ?? '');
    if ($value === '') {
        throw new InvalidArgumentException(USAGE);
    }

    return $value;
}

/** @param list<Book> $books */
function listText(array $books): string
{
    $lines = array_map(fn(Book $book): string => $book->line() . "\n", sortedByTitle($books));

    return implode('', $lines) . count($books) . " books\n";
}

/** @param list<Book> $books */
function searchLine(array $books, string $search, ?string $author = null): string
{
    $found = searchTitles($books, $search, $author);
    $by = $author === null ? '' : " by \"$author\"";

    return "Search \"$search\"$by: " . ($found === [] ? 'nothing found' : implode(', ', $found));
}

/** @param list<Book> $books */
function lendAndSave(array $books, string $title, string $path, DateTimeImmutable $today): string
{
    $book = lend($books, $title);
    saveBooks($path, $books);

    return "Lent: $book->title, due back on " . dueDate($today)->format('l, j F Y') . "\n";
}

/**
 * @param list<Book> $books
 * @param list<string> $fields
 */
function addAndSave(array $books, array $fields, string $path): string
{
    $books = addBook($books, $fields);
    saveBooks($path, $books);

    return 'Added: ' . $books[count($books) - 1]->title . "\n";
}

/**
 * Runs one command and returns the text to print.
 *
 * @param list<string> $args the words after the script's name
 */
function run(array $args, string $path, DateTimeImmutable $today): string
{
    $command = $args[0] ?? 'list';
    $books = loadBooks($path);

    return match ($command) {
        'list' => listText($books),
        'search' => searchLine($books, required($args, 1), $args[2] ?? null) . "\n",
        'lend' => lendAndSave($books, required($args, 1), $path, $today),
        'add' => addAndSave($books, array_slice($args, 1), $path),
        default => throw new InvalidArgumentException("Unknown command \"$command\"\n" . USAGE),
    };
}

try {
    echo run(array_slice($argv, 1), __DIR__ . '/books.json', new DateTimeImmutable('today'));
} catch (InvalidArgumentException|BookNotFound|NoCopyLeft $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
