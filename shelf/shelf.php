<?php

declare(strict_types=1);

// Shelf: a small lending library. Chapter 36: classes in src/, loaded by Composer's autoloader.
// Once, in the folder shelf/: composer dump-autoload
// Run: php shelf/shelf.php [list | search <text> [<author>] | lend <title> <member> | add <title> <author> <year> [<isbn>]]

use Shelf\Book;
use Shelf\JsonBookRepository;
use Shelf\Library;
use Shelf\ShelfException;

use function Shelf\loadMembers;

require __DIR__ . '/vendor/autoload.php';

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
    $lines = array_map(fn(Book $book): string => $book->line() . "\n", $books);

    return implode('', $lines) . count($books) . " books\n";
}

function searchLine(Library $library, string $search, ?string $author = null): string
{
    $found = $library->search($search, $author);
    $by = $author === null ? '' : " by \"$author\"";

    return "Search \"$search\"$by: " . ($found === [] ? 'nothing found' : implode(', ', $found));
}

/**
 * Runs one command and returns the text to print.
 *
 * @param list<string> $args the words after the script's name
 */
function run(array $args, Library $library, DateTimeImmutable $today): string
{
    $command = $args[0] ?? 'list';

    return match ($command) {
        'list' => listText($library->books()),
        'search' => searchLine($library, required($args, 1), $args[2] ?? null) . "\n",
        'lend' => $library->lend(required($args, 1), required($args, 2), $today)->line() . "\n",
        'add' => 'Added: ' . $library->add(array_slice($args, 1))->title . "\n",
        default => throw new InvalidArgumentException("Unknown command \"$command\"\n" . USAGE),
    };
}

try {
    $library = new Library(new JsonBookRepository(__DIR__ . '/books.json'), loadMembers(__DIR__ . '/members.json'));
    echo run(array_slice($argv, 1), $library, new DateTimeImmutable('today'));
} catch (InvalidArgumentException|ShelfException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
