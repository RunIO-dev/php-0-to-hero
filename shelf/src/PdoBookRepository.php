<?php

declare(strict_types=1);

namespace Shelf;

use PDO;

/**
 * The books in the database's books table (chapters 54 and 55). Every value goes in through a
 * placeholder. The title column compares without case (COLLATE NOCASE), so 'kindred' finds Kindred.
 */
final readonly class PdoBookRepository implements BookRepository
{
    public function __construct(private PDO $db) {}

    public function all(): array
    {
        $rows = $this->db->query('SELECT title, author, year, copies, isbn FROM books ORDER BY id');

        return array_values(array_map(fn(mixed $row): Book => Book::fromArray($row), $rows === false ? [] : $rows->fetchAll()));
    }

    public function find(string $title): ?Book
    {
        $find = $this->db->prepare('SELECT title, author, year, copies, isbn FROM books WHERE title = :title');
        $find->execute(['title' => trim($title)]);
        $row = $find->fetch();

        return $row === false ? null : Book::fromArray($row);
    }

    public function add(Book $book): void
    {
        $add = $this->db->prepare('INSERT INTO books (title, author, year, copies, isbn) VALUES (:title, :author, :year, :copies, :isbn)');
        $add->execute($book->toArray());
    }

    public function update(Book $book): void
    {
        $update = $this->db->prepare('UPDATE books SET author = :author, year = :year, copies = :copies, isbn = :isbn WHERE title = :title');
        $update->execute($book->toArray());
        if ($update->rowCount() === 0) {
            throw new BookNotFound("There is no book titled \"$book->title\"");
        }
    }

    public function remove(string $title): void
    {
        $remove = $this->db->prepare('DELETE FROM books WHERE title = :title');
        $remove->execute(['title' => trim($title)]);
        if ($remove->rowCount() === 0) {
            throw new BookNotFound("There is no book titled \"$title\"");
        }
    }
}
