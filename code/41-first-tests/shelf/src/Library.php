<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use InvalidArgumentException;

/** The library: its books and members, and the rules for lending and adding. It has a repository; it isn't one. */
final readonly class Library
{
    /** @param list<Member> $members */
    public function __construct(private BookRepository $repository, private array $members) {}

    /** @return list<Book> every book, sorted by title */
    public function books(): array
    {
        $books = $this->repository->all();
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
            $this->repository->all(),
            fn(Book $book): bool => $contains($book->title, $text)
                && ($author === null || $contains($book->author, $author)),
        );

        return array_column($found, 'title');
    }

    public function lend(string $title, string $name, DateTimeImmutable $today): Loan
    {
        $member = $this->member($name);
        $books = $this->repository->all();
        $book = $this->find($books, $title);
        $book->lend();
        $this->repository->save($books);

        return Loan::start($book, $member, $today);
    }

    /** @param list<string> $fields title, author, year and, optionally, the ISBN */
    public function add(array $fields): Book
    {
        $books = $this->repository->all();
        $new = $this->newBook($fields);
        foreach ($books as $book) {
            $sameIsbn = $new->isbn !== null && $book->isbn !== null && $book->isbn->equals($new->isbn);
            if ($sameIsbn || sameTitle($book->title, $new->title)) {
                throw new InvalidArgumentException("\"$book->title\" is already on the shelf");
            }
        }
        $books[] = $new;
        $this->repository->save($books);

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

    /** @param list<Book> $books */
    private function find(array $books, string $title): Book
    {
        foreach ($books as $book) {
            if (sameTitle($book->title, $title)) {
                return $book;
            }
        }

        throw new BookNotFound("There is no book titled \"$title\"");
    }

    private function member(string $name): Member
    {
        foreach ($this->members as $member) {
            if (mb_strtolower($member->name) === mb_strtolower(trim($name))) {
                return $member;
            }
        }

        throw new MemberNotFound("There is no member named \"$name\"");
    }
}
