<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * The library: its books, members and loans, and the rules for lending and adding. It has
 * repositories; it isn't one. Chapter 56: members and loans are stored too.
 */
final readonly class Library
{
    /** The most copies of one book the form accepts: more is a typing mistake. */
    public const int MOST_COPIES = 999;

    public function __construct(
        private BookRepository $books,
        private MemberRepository $members,
        private LoanRepository $loans,
    ) {}

    /** @return list<Book> every book, sorted by title */
    public function books(): array
    {
        $books = $this->books->all();
        usort($books, fn(Book $a, Book $b): int => strcasecmp($a->title, $b->title));

        return $books;
    }

    /**
     * Every title that contains the search text, in any case, optionally only by one author.
     *
     * @return list<string>
     */
    public function search(string $text, ?string $author = null): array
    {
        $contains = fn(string $haystack, string $part): bool
            => str_contains(mb_strtolower($haystack), mb_strtolower($part));
        $found = array_filter(
            $this->books->all(),
            fn(Book $book): bool => $contains($book->title, $text)
                && ($author === null || $contains($book->author, $author)),
        );

        return array_column($found, 'title');
    }

    /**
     * One page of the books that match the query, sorted by title (chapter 70), and how many pages
     * there are. $page counts from 1.
     *
     * @return array{books: list<Book>, pages: int}
     */
    public function page(BookQuery $query, int $page, int $perPage): array
    {
        if ($page < 1 || $perPage < 1) {
            throw new InvalidArgumentException('Pages count from 1, and hold at least one book');
        }

        return [
            'books' => $this->books->page($query, ($page - 1) * $perPage, $perPage),
            'pages' => max(1, (int) ceil($this->books->count($query) / $perPage)),
        ];
    }

    /** The book with this title, in any case. */
    public function find(string $title): Book
    {
        return $this->books->find($title) ?? throw new BookNotFound("There is no book titled \"$title\"");
    }

    public function lend(string $title, string $name, DateTimeImmutable $today): Loan
    {
        $member = $this->member($name);
        $book = $this->find($title);
        if (!$book->isAvailable()) {
            throw new NoCopyLeft("All copies of \"$book->title\" are on loan");
        }
        $loan = Loan::start($book, $member, $today);
        $this->loans->add($loan); // stores the loan and takes the copy, together (chapter 57)

        return $loan;
    }

    /** @return list<Loan> the member's loans, the soonest due first */
    public function loansOf(string $name): array
    {
        return $this->loans->forMember($this->member($name)->name);
    }

    /** Sets how many copies the library has of a book. $copies is as typed into the form. */
    public function changeCopies(string $title, string $copies): Book
    {
        $book = $this->find($title);
        $count = filter_var($copies, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => self::MOST_COPIES]]);
        if ($count === false) {
            throw new InvalidArgumentException('Copies must be a whole number from 0 to ' . self::MOST_COPIES . '.');
        }
        $book->changeCopies($count);
        $this->books->update($book);

        return $book;
    }

    /** Takes a book off the shelf for good, and returns it. A book that is lent can't go: its loans need it. */
    public function remove(string $title): Book
    {
        $book = $this->find($title);
        if ($this->loans->isOnLoan($book->title)) {
            throw new InvalidArgumentException("\"$book->title\" is on loan, so it can't be removed.");
        }
        $this->books->remove($book->title);

        return $book;
    }

    /** @param list<string> $fields title, author, year and, optionally, the ISBN */
    public function add(array $fields): Book
    {
        $books = $this->books->all();
        $new = $this->newBook($fields);
        foreach ($books as $book) {
            $sameIsbn = $new->isbn !== null && $book->isbn !== null && $book->isbn->equals($new->isbn);
            if ($sameIsbn || sameTitle($book->title, $new->title)) {
                throw new BookExists("\"$book->title\" is already on the shelf");
            }
        }
        $this->books->add($new);

        return $new;
    }

    /** @param list<string> $fields */
    private function newBook(array $fields): Book
    {
        $title = tidy($fields[0] ?? '');
        $author = tidy($fields[1] ?? '');
        $range = ['min_range' => Book::FIRST_YEAR, 'max_range' => Book::LAST_YEAR];
        $year = filter_var($fields[2] ?? '', FILTER_VALIDATE_INT, ['options' => $range]);
        if ($title === '' || $author === '' || $year === false) {
            throw new InvalidArgumentException('A book needs a title, an author and a year from ' . implode(' to ', $range) . '.');
        }

        return new Book($title, $author, $year, isbn: isset($fields[3]) ? new Isbn($fields[3]) : null);
    }

    /** The member with this name, or null: for the website, which knows a member by the name in the session (chapter 63). */
    public function findMember(string $name): ?Member
    {
        return $this->members->find($name);
    }

    private function member(string $name): Member
    {
        return $this->members->find($name) ?? throw new MemberNotFound("There is no member named \"$name\"");
    }
}
