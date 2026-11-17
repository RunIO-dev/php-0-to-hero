<?php

declare(strict_types=1);

// Shelf: a small lending library. Chapter 32: named constructors and class constants.
// Run: php shelf/shelf.php [list | search <text> [<author>] | lend <title> <member> | add <title> <author> <year> [<isbn>]]

require_once __DIR__ . '/data.php';
require_once __DIR__ . '/library.php';

const USAGE = <<<TXT
    Usage: php shelf/shelf.php list
           php shelf/shelf.php search <text> [<author>]
           php shelf/shelf.php lend <title> <member>
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
function lendAndSave(array $books, string $title, string $name, string $folder, DateTimeImmutable $today): string
{
    $member = findMember(loadMembers("$folder/members.json"), $name);
    $loan = lend($books, $title, $member, $today);
    saveBooks("$folder/books.json", $books);

    return $loan->line() . "\n";
}

/**
 * @param list<Book> $books
 * @param list<string> $fields
 */
function addAndSave(array $books, array $fields, string $folder): string
{
    $books = addBook($books, $fields);
    saveBooks("$folder/books.json", $books);

    return 'Added: ' . $books[count($books) - 1]->title . "\n";
}

/**
 * Runs one command and returns the text to print.
 *
 * @param list<string> $args the words after the script's name
 */
function run(array $args, string $folder, DateTimeImmutable $today): string
{
    $command = $args[0] ?? 'list';
    $books = loadBooks("$folder/books.json");

    return match ($command) {
        'list' => listText($books),
        'search' => searchLine($books, required($args, 1), $args[2] ?? null) . "\n",
        'lend' => lendAndSave($books, required($args, 1), required($args, 2), $folder, $today),
        'add' => addAndSave($books, array_slice($args, 1), $folder),
        default => throw new InvalidArgumentException("Unknown command \"$command\"\n" . USAGE),
    };
}

try {
    echo run(array_slice($argv, 1), __DIR__, new DateTimeImmutable('today'));
} catch (InvalidArgumentException|BookNotFound|MemberNotFound|NoCopyLeft $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
