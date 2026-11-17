<?php

declare(strict_types=1);

// Shelf's rules: books, members and loans. No input, no output, no files.
// What the rules need from storage is the interface BookRepository; data.php implements it.

final class BookNotFound extends RuntimeException {}

final class MemberNotFound extends RuntimeException {}

final class NoCopyLeft extends RuntimeException {}

/** What a member is to the library. The role decides how long a loan lasts. */
enum Role: string
{
    case Reader = 'reader';
    case Staff = 'staff';

    public function loanDays(): int
    {
        return match ($this) {
            Role::Reader => 14,
            Role::Staff => 28,
        };
    }
}

/** One book on the shelf. Only the book changes its number of copies, so it never goes below 0. */
final class Book
{
    public const int FIRST_YEAR = 1;
    public const int LAST_YEAR = 2100;

    public function __construct(
        public readonly string $title,
        public readonly string $author,
        public readonly int $year,
        public private(set) int $copies = 1,
        public readonly ?Isbn $isbn = null,
    ) {
        if ($copies < 0) {
            throw new InvalidArgumentException("\"$title\" can't have $copies copies");
        }
        if ($year < self::FIRST_YEAR || $year > self::LAST_YEAR) {
            throw new InvalidArgumentException("\"$title\" can't be from the year $year");
        }
    }

    /** A book from the data in books.json, checked: the file is input, like anything from outside. */
    public static function fromArray(mixed $data): self
    {
        $isbn = is_array($data) ? $data['isbn'] ?? null : null;
        if (!is_array($data) || !is_string($data['title'] ?? null) || !is_string($data['author'] ?? null)
            || !is_int($data['year'] ?? null) || !is_int($data['copies'] ?? null) || !($isbn === null || is_string($isbn))) {
            throw new UnexpectedValueException('Each book needs a title, an author, a year and copies: ' . json_encode($data));
        }

        return new self($data['title'], $data['author'], $data['year'], $data['copies'], $isbn === null ? null : new Isbn($isbn));
    }

    /** @return array{title: string, author: string, year: int, copies: int, isbn: ?string} the data that fromArray reads */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'author' => $this->author,
            'year' => $this->year,
            'copies' => $this->copies,
            'isbn' => $this->isbn?->digits,
        ];
    }

    public function isAvailable(): bool
    {
        return $this->copies > 0;
    }

    public function lend(): void
    {
        if (!$this->isAvailable()) {
            throw new NoCopyLeft("All copies of \"$this->title\" are on loan");
        }
        $this->copies--;
    }

    public function line(): string
    {
        $status = $this->isAvailable() ? 'available' : 'all copies on loan';

        return "- $this->title ($this->author, $this->year): $status";
    }
}

/** An ISBN-13, valid from the start: the regex checks the shape, arithmetic the last digit. */
final readonly class Isbn
{
    public string $digits;

    public function __construct(string $input)
    {
        $digits = str_replace(['-', ' '], '', $input);
        if (!$this->isValid($digits)) {
            throw new InvalidArgumentException("\"$input\" is not a valid ISBN-13");
        }
        $this->digits = $digits;
    }

    public function equals(Isbn $other): bool
    {
        return $this->digits === $other->digits;
    }

    private function isValid(string $digits): bool
    {
        if (preg_match('/^97[89]\d{10}$/D', $digits) !== 1) {
            return false;
        }
        $sum = 0;
        foreach (str_split(substr($digits, 0, 12)) as $i => $digit) {
            $sum += (int) $digit * ($i % 2 === 0 ? 1 : 3);
        }

        return (10 - $sum % 10) % 10 === (int) $digits[12];
    }
}

/** Someone who may borrow books. */
final readonly class Member
{
    public function __construct(public string $name, public Role $role) {}

    /** A member from the data in members.json, checked. */
    public static function fromArray(mixed $data): self
    {
        $role = is_array($data) && is_string($data['role'] ?? null) ? Role::tryFrom($data['role']) : null;
        if (!is_array($data) || !is_string($data['name'] ?? null) || $role === null) {
            throw new UnexpectedValueException('Each member needs a name and a role, reader or staff: ' . json_encode($data));
        }

        return new self($data['name'], $role);
    }
}

/** One book lent to one member, until a due date. */
final readonly class Loan
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

/** Where the books are kept. The rules use this interface, and don't know whether it is a file or, later, a database. */
interface BookRepository
{
    /** @return list<Book> */
    public function all(): array;

    /** @param list<Book> $books */
    public function save(array $books): void;
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

/**
 * Lends one copy of $title to $member. The book in the list changes too: it is the same object.
 *
 * @param list<Book> $books
 */
function lend(array $books, string $title, Member $member, DateTimeImmutable $today): Loan
{
    $book = findBook($books, $title);
    $book->lend();

    return new Loan($book, $member, $today->modify('+' . $member->role->loanDays() . ' days'));
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
    $range = ['min_range' => Book::FIRST_YEAR, 'max_range' => Book::LAST_YEAR];
    $year = filter_var($fields[2] ?? '', FILTER_VALIDATE_INT, ['options' => $range]);
    if ($title === '' || $author === '' || $year === false) {
        throw new InvalidArgumentException('A book needs a title, an author and a year from ' . implode(' to ', $range) . '.');
    }
    $isbn = isset($fields[3]) ? new Isbn($fields[3]) : null;
    foreach ($books as $book) {
        $sameIsbn = $isbn !== null && $book->isbn !== null && $book->isbn->equals($isbn);
        if ($sameIsbn || sameTitle($book->title, $title)) {
            throw new InvalidArgumentException("\"$book->title\" is already on the shelf");
        }
    }
    $books[] = new Book($title, $author, $year, isbn: $isbn);

    return $books;
}
