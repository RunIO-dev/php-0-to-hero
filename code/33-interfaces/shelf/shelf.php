<?php

declare(strict_types=1);

// Shelf: a small lending library. Chapter 33: the books come from a BookRepository.
// Run: php shelf/shelf.php [list | search <text> [<author>] | lend <title> <member> | add <title> <author> <year> [<isbn>]]

require_once __DIR__ . '/library.php';
require_once __DIR__ . '/data.php';

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

/** @param list<Member> $members */
function lendAndSave(BookRepository $repository, array $members, string $title, string $name, DateTimeImmutable $today): string
{
    $books = $repository->all();
    $loan = lend($books, $title, findMember($members, $name), $today);
    $repository->save($books);

    return $loan->line() . "\n";
}

/** @param list<string> $fields */
function addAndSave(BookRepository $repository, array $fields): string
{
    $books = addBook($repository->all(), $fields);
    $repository->save($books);

    return 'Added: ' . $books[count($books) - 1]->title . "\n";
}

/**
 * Runs one command and returns the text to print.
 *
 * @param list<string> $args the words after the script's name
 */
function run(array $args, BookRepository $repository, string $membersPath, DateTimeImmutable $today): string
{
    $command = $args[0] ?? 'list';

    return match ($command) {
        'list' => listText($repository->all()),
        'search' => searchLine($repository->all(), required($args, 1), $args[2] ?? null) . "\n",
        'lend' => lendAndSave($repository, loadMembers($membersPath), required($args, 1), required($args, 2), $today),
        'add' => addAndSave($repository, array_slice($args, 1)),
        default => throw new InvalidArgumentException("Unknown command \"$command\"\n" . USAGE),
    };
}

try {
    $repository = new JsonBookRepository(__DIR__ . '/books.json');
    echo run(array_slice($argv, 1), $repository, __DIR__ . '/members.json', new DateTimeImmutable('today'));
} catch (InvalidArgumentException|BookNotFound|MemberNotFound|NoCopyLeft $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
