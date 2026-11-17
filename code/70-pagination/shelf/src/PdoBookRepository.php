<?php

declare(strict_types=1);

namespace Shelf;

use PDO;
use PDOStatement;

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

    /** An empty filter switches itself off, as (:author = '' OR ...): one statement for every query. */
    private const string MATCHES = "(:author = '' OR author LIKE :pattern ESCAPE '\\') AND (:available = 0 OR copies > 0)";

    public function page(BookQuery $query, int $offset, int $limit): array
    {
        $find = $this->db->prepare('SELECT title, author, year, copies, isbn FROM books WHERE ' . self::MATCHES
            . ' ORDER BY title COLLATE NOCASE LIMIT :limit OFFSET :offset');
        $this->bindQuery($find, $query);
        $find->bindValue('limit', $limit, PDO::PARAM_INT);
        $find->bindValue('offset', $offset, PDO::PARAM_INT);
        $find->execute();

        return array_values(array_map(fn(mixed $row): Book => Book::fromArray($row), $find->fetchAll()));
    }

    public function count(BookQuery $query): int
    {
        $count = $this->db->prepare('SELECT COUNT(*) FROM books WHERE ' . self::MATCHES);
        $this->bindQuery($count, $query);
        $count->execute();

        return (int) $count->fetchColumn();
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

    /** LIKE finds part of the name; % and _ that a visitor types are taken as letters, not as wildcards. */
    private function bindQuery(PDOStatement $statement, BookQuery $query): void
    {
        $statement->bindValue('author', $query->author);
        $statement->bindValue('pattern', '%' . addcslashes($query->author, '%_\\') . '%');
        $statement->bindValue('available', $query->availableOnly ? 1 : 0, PDO::PARAM_INT);
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
