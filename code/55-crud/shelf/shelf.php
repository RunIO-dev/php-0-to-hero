<?php

declare(strict_types=1);

// Shelf: a small lending library. Chapter 36: classes in src/, loaded by Composer's autoloader.
// Chapter 54: the books are in a database, var/shelf.sqlite; "import" moves books.json into it once.
// Chapter 55: "remove" takes a book off the shelf.
// Once, in the folder shelf/: composer dump-autoload
// Run: php shelf/shelf.php [list | search <text> [<author>] | lend <title> <member> | add <title> <author> <year> [<isbn>] | remove <title> | import]

use Shelf\Book;
use Shelf\BookRepository;
use Shelf\Library;
use Shelf\PdoBookRepository;
use Shelf\ShelfException;

use function Shelf\loadMembers;
use function Shelf\openDatabase;
use function Shelf\readList;

require __DIR__ . '/vendor/autoload.php';

const USAGE = <<<TXT
    Usage: php shelf/shelf.php list
           php shelf/shelf.php search <text> [<author>]
           php shelf/shelf.php lend <title> <member>
           php shelf/shelf.php add <title> <author> <year> [<isbn>]
           php shelf/shelf.php remove <title>
           php shelf/shelf.php import
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
 * Adds books to a repository that has none yet.
 *
 * @param list<Book> $books
 */
function import(array $books, BookRepository $to): string
{
    if ($to->all() !== []) {
        throw new InvalidArgumentException('The database already has books; nothing was imported.');
    }
    foreach ($books as $book) {
        $to->add($book);
    }

    return 'Imported ' . count($books) . " books from books.json\n";
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
        'remove' => 'Removed: ' . $library->remove(required($args, 1))->title . "\n",
        default => throw new InvalidArgumentException("Unknown command \"$command\"\n" . USAGE),
    };
}

try {
    $books = new PdoBookRepository(openDatabase(__DIR__ . '/var/shelf.sqlite'));
    if (($argv[1] ?? '') === 'import') {
        echo import(array_map(Book::fromArray(...), readList(__DIR__ . '/books.json')), $books);
    } else {
        $library = new Library($books, loadMembers(__DIR__ . '/members.json'));
        echo run(array_slice($argv, 1), $library, new DateTimeImmutable('today'));
    }
} catch (InvalidArgumentException|ShelfException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
