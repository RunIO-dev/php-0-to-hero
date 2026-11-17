<?php

declare(strict_types=1);

namespace Shelf;

/**
 * Where the books are kept. The rules use this interface, and don't know whether it is a database
 * or, in the tests, an array. Chapter 55: one method per change, so a change writes only its book.
 * A book is found by its title, in any case.
 */
interface BookRepository
{
    /** @return list<Book> */
    public function all(): array;

    /**
     * One page of the books that match the query, sorted by title (chapter 70): $offset of them
     * skipped, then at most $limit.
     *
     * @return list<Book>
     */
    public function page(BookQuery $query, int $offset, int $limit): array;

    /** How many books match the query. */
    public function count(BookQuery $query): int;

    public function find(string $title): ?Book;

    public function add(Book $book): void;

    /** Stores the book's new values. Throws BookNotFound if there is no book with its title. */
    public function update(Book $book): void;

    /** Throws BookNotFound if there is no book with this title. */
    public function remove(string $title): void;
}
