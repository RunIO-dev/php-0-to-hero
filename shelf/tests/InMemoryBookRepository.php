<?php

declare(strict_types=1);

namespace Shelf\Tests;

use Shelf\Book;
use Shelf\BookRepository;

/**
 * A repository for tests: the books live in an array, not in a file. all() returns
 * copies, as a file would, so a change counts only once it is saved.
 */
final class InMemoryBookRepository implements BookRepository
{
    /** @param list<Book> $books */
    public function __construct(private array $books = []) {}

    public function all(): array
    {
        return array_map(fn(Book $book): Book => clone $book, $this->books);
    }

    public function save(array $books): void
    {
        $this->books = $books;
    }
}
