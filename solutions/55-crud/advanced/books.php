<?php

declare(strict_types=1);

// Exercise 55, advanced: books as objects, stored by one method.

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
        $values = ['title' => $book->title, 'author' => $book->author, 'year' => $book->year, 'copies' => $book->copies];
        if ($book->id === null) {
            $insert = $this->db->prepare('INSERT INTO books (title, author, year, copies) VALUES (:title, :author, :year, :copies)');
            $insert->execute($values);

            return $book->withId((int) $this->db->lastInsertId());
        }
        $update = $this->db->prepare('UPDATE books SET title = :title, author = :author, year = :year, copies = :copies WHERE id = :id');
        $update->execute([...$values, 'id' => $book->id]);
        if ($update->rowCount() === 0) {
            throw new OutOfBoundsException("There is no book $book->id");
        }

        return $book;
    }

    public function find(int $id): ?Book
    {
        $find = $this->db->prepare('SELECT id, title, author, year, copies FROM books WHERE id = :id');
        $find->execute(['id' => $id]);
        $row = $find->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : self::book($row);
    }

    /** @return list<Book> */
    public function all(): array
    {
        $rows = $this->db->query('SELECT id, title, author, year, copies FROM books ORDER BY title');

        return array_values(array_map(self::book(...), $rows === false ? [] : $rows->fetchAll(PDO::FETCH_ASSOC)));
    }

    public function delete(Book $book): void
    {
        if ($book->id === null) {
            throw new LogicException("\"$book->title\" was never stored");
        }
        $delete = $this->db->prepare('DELETE FROM books WHERE id = :id');
        $delete->execute(['id' => $book->id]);
    }

    private static function book(mixed $row): Book
    {
        if (!is_array($row) || !is_string($row['title'] ?? null) || !is_string($row['author'] ?? null)
            || !is_int($row['year'] ?? null) || !is_int($row['copies'] ?? null) || !is_int($row['id'] ?? null)) {
            throw new UnexpectedValueException('Not a book: ' . json_encode($row));
        }

        return new Book($row['title'], $row['author'], $row['year'], $row['copies'], $row['id']);
    }
}
