<?php

declare(strict_types=1);

// Shelf: a small lending library. Chapter 36: classes in src/, loaded by Composer's autoloader.
// Chapter 54: the books are in a database, var/shelf.sqlite; "import" moves books.json into it once.
// Chapter 55: "remove" takes a book off the shelf.
// Chapter 56: members and loans are in the database too; "loans" lists a member's loans.
// Chapter 57: "import" runs in one transaction, so a mistake in the files imports nothing.
// Once, in the folder shelf/: composer dump-autoload
// Run: php shelf/shelf.php [list | search <text> [<author>] | lend <title> <member> | add <title> <author> <year> [<isbn>] | remove <title> | loans <member> | import]

use Shelf\Book;
use Shelf\BookRepository;
use Shelf\Library;
use Shelf\Loan;
use Shelf\Member;
use Shelf\MemberRepository;
use Shelf\PdoBookRepository;
use Shelf\PdoLoanRepository;
use Shelf\PdoMemberRepository;
use Shelf\ShelfException;

use function Shelf\inTransaction;
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
           php shelf/shelf.php loans <member>
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
 * Adds the books and members from the JSON files to a database that has no books yet.
 *
 * @param list<Book> $books
 * @param list<Member> $members
 */
function import(array $books, array $members, BookRepository $bookRepository, MemberRepository $memberRepository): string
{
    if ($bookRepository->all() !== []) {
        throw new InvalidArgumentException('The database already has books; nothing was imported.');
    }
    foreach ($books as $book) {
        $bookRepository->add($book);
    }
    foreach ($members as $member) {
        $memberRepository->add($member);
    }

    return 'Imported ' . count($books) . ' books and ' . count($members) . " members\n";
}

/** @param list<Loan> $loans */
function loansText(string $name, array $loans): string
{
    if ($loans === []) {
        return "$name has no books on loan\n";
    }
    $lines = array_map(fn(Loan $loan): string => "- {$loan->book->title}, due back on {$loan->due->format('l, j F Y')}\n", $loans);

    return "$name has:\n" . implode('', $lines);
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
        'loans' => loansText(required($args, 1), $library->loansOf(required($args, 1))),
        default => throw new InvalidArgumentException("Unknown command \"$command\"\n" . USAGE),
    };
}

try {
    $db = openDatabase(__DIR__ . '/var/shelf.sqlite');
    $books = new PdoBookRepository($db);
    $members = new PdoMemberRepository($db);
    if (($argv[1] ?? '') === 'import') {
        $fileBooks = array_map(Book::fromArray(...), readList(__DIR__ . '/books.json'));
        $fileMembers = loadMembers(__DIR__ . '/members.json');
        echo inTransaction($db, fn(): string => import($fileBooks, $fileMembers, $books, $members));
    } else {
        $library = new Library($books, $members, new PdoLoanRepository($db));
        echo run(array_slice($argv, 1), $library, new DateTimeImmutable('today'));
    }
} catch (InvalidArgumentException|ShelfException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
