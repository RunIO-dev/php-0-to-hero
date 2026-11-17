<?php

declare(strict_types=1);

// Shelf's rules: books, members and loans. No input, no output, no files.

final class BookNotFound extends RuntimeException {}

final class MemberNotFound extends RuntimeException {}

final class NoCopyLeft extends RuntimeException {}

const LOAN_DAYS = 14;

/** One book on the shelf. */
final class Book
{
    public function __construct(
        public string $title,
        public string $author,
        public int $year,
        public int $copies = 1,
        public ?string $isbn = null,
    ) {}

    public function isAvailable(): bool
    {
        return $this->copies > 0;
    }

    public function line(): string
    {
        $status = $this->isAvailable() ? 'available' : 'all copies on loan';

        return "- $this->title ($this->author, $this->year): $status";
    }
}

/** Someone who may borrow books. */
final class Member
{
    public function __construct(public string $name) {}
}

/** One book lent to one member, until a due date. */
final class Loan
{
    public function __construct(
        public Book $book,
        public Member $member,
        public DateTimeImmutable $due,
    ) {}

    public function line(): string
    {
        return "Lent: {$this->book->title} to {$this->member->name}, due back on {$this->due->format('l, j F Y')}";
    }
}

/** Two titles are the same in any case, and with spaces around them. */
function sameTitle(string $a, string $b): bool
{
    return mb_strtolower(trim($a)) === mb_strtolower(trim($b));
}

/**
 * @param list<Book> $books
 * @return list<Book>
 */
function sortedByTitle(array $books): array
{
    usort($books, fn(Book $a, Book $b): int => strcasecmp($a->title, $b->title));

    return $books;
}

/**
 * Every title that contains the search text, in any case, optionally only by one author.
 *
 * @param list<Book> $books
 * @return list<string>
 */
function searchTitles(array $books, string $search, ?string $author = null): array
{
    $contains = fn(string $text, string $part): bool
        => str_contains(mb_strtolower($text), mb_strtolower($part));
    $found = array_filter(
        $books,
        fn(Book $book): bool => $contains($book->title, $search)
            && ($author === null || $contains($book->author, $author)),
    );

    return array_column($found, 'title');
}

/** @param list<Book> $books */
function findBook(array $books, string $title): Book
{
    foreach ($books as $book) {
        if (sameTitle($book->title, $title)) {
            return $book;
        }
    }

    throw new BookNotFound("There is no book titled \"$title\"");
}

/** @param list<Member> $members */
function findMember(array $members, string $name): Member
{
    foreach ($members as $member) {
        if (mb_strtolower($member->name) === mb_strtolower(trim($name))) {
            return $member;
        }
    }

    throw new MemberNotFound("There is no member named \"$name\"");
}

function dueDate(DateTimeImmutable $lentOn): DateTimeImmutable
{
    return $lentOn->modify('+' . LOAN_DAYS . ' days');
}

/**
 * Lends one copy of $title to $member. The book in the list changes too: it is the same object.
 *
 * @param list<Book> $books
 */
function lend(array $books, string $title, Member $member, DateTimeImmutable $today): Loan
{
    $book = findBook($books, $title);
    if (!$book->isAvailable()) {
        throw new NoCopyLeft("All copies of \"$book->title\" are on loan");
    }
    $book->copies--;

    return new Loan($book, $member, dueDate($today));
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
 * @param list<Book> $books
 * @param list<string> $fields title, author, year and, optionally, the ISBN
 * @return list<Book>
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
    if (array_filter($books, fn(Book $book): bool => sameTitle($book->title, $title)) !== []) {
        throw new InvalidArgumentException("\"$title\" is already on the shelf");
    }
    $books[] = new Book($title, $author, $year, isbn: $isbn);

    return $books;
}
