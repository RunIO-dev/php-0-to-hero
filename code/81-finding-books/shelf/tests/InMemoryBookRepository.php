<?php

declare(strict_types=1);

namespace Shelf\Tests;

use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\BookQuery;
use Shelf\BookRepository;

use function Shelf\sameTitle;

/**
 * A repository for tests: the books live in an array, not in a database. It hands out copies
 * of its books, as a database would, so a change counts only once it is stored with update().
 */
final class InMemoryBookRepository implements BookRepository
{
    /** @param list<Book> $books */
    public function __construct(private array $books = []) {}

    public function all(): array
    {
        return array_map(fn(Book $book): Book => clone $book, $this->books);
    }

    public function page(BookQuery $query, int $offset, int $limit): array
    {
        $found = array_values(array_filter($this->all(), $query->matches(...)));
        usort($found, $query->sort->compare(...));

        return array_slice($found, $offset, $limit);
    }

    public function count(BookQuery $query): int
    {
        return count(array_filter($this->books, $query->matches(...)));
    }

    public function find(string $title): ?Book
    {
        $at = $this->position($title);

        return $at === null ? null : clone $this->books[$at];
    }

    public function add(Book $book): void
    {
        $this->books[] = clone $book;
    }

    public function update(Book $book): void
    {
        if ($this->position($book->title) === null) {
            throw new BookNotFound("There is no book titled \"$book->title\"");
        }
        $this->books = array_map(fn(Book $old): Book => sameTitle($old->title, $book->title) ? clone $book : $old, $this->books);
    }

    public function remove(string $title): void
    {
        $at = $this->position($title) ?? throw new BookNotFound("There is no book titled \"$title\"");
        array_splice($this->books, $at, 1);
    }

    private function position(string $title): ?int
    {
        foreach ($this->books as $at => $book) {
            if (sameTitle($book->title, $title)) {
                return $at;
            }
        }

        return null;
    }
}
