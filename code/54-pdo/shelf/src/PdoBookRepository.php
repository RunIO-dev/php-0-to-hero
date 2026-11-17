<?php

declare(strict_types=1);

namespace Shelf;

use PDO;

/** The books in the database's books table (chapter 54). Every value goes in through a placeholder. */
final readonly class PdoBookRepository implements BookRepository
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        $rows = $this->db->query('SELECT title, author, year, copies, isbn FROM books ORDER BY id');

        return array_values(array_map(fn(mixed $row): Book => Book::fromArray($row), $rows === false ? [] : $rows->fetchAll()));
    }

    /** Adds the books that are new, and updates the others, found by their title. */
    public function save(array $books): void
    {
        $save = $this->db->prepare(
            'INSERT INTO books (title, author, year, copies, isbn) VALUES (:title, :author, :year, :copies, :isbn)
             ON CONFLICT (title) DO UPDATE
             SET author = excluded.author, year = excluded.year, copies = excluded.copies, isbn = excluded.isbn',
        );
        foreach ($books as $book) {
            $save->execute($book->toArray());
        }
    }
}
