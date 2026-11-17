<?php

declare(strict_types=1);

// Exercise 55, advanced: books as objects, stored by one method.
//
// A Book without an id is new; one with an id is stored already.
// save($book):  a new book is inserted, and save() returns it with the id the database gave it;
//               a stored one is updated (every column), and save() returns it as it is. A stored
//               book whose id isn't in the table: OutOfBoundsException.
// find($id):    the Book, or null.
// all():        every Book, in alphabetical order of title.
// delete($book): removes a stored book. A book without an id: LogicException.

final readonly class Book
{
    public function __construct(
        public string $title,
        public string $author,
        public int $year,
        public int $copies = 1,
        public ?int $id = null,
    ) {}

    public function withId(int $id): self
    {
        return new self($this->title, $this->author, $this->year, $this->copies, $id);
    }
}

final readonly class PdoBooks
{
    public function __construct(private PDO $db) {}

    public function save(Book $book): Book
    {
        return $book;
    }

    public function find(int $id): ?Book
    {
        return null;
    }

    /** @return list<Book> */
    public function all(): array
    {
        return [];
    }

    public function delete(Book $book): void {}
}
// Check your work: composer exercise 55 advanced
