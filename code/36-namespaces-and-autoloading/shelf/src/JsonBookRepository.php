<?php

declare(strict_types=1);

namespace Shelf;

use RuntimeException;

/** The books in a JSON file: a list of objects with title, author, year, copies and an optional isbn. */
final readonly class JsonBookRepository implements BookRepository
{
    public function __construct(private string $path) {}

    public function all(): array
    {
        return array_map(fn(mixed $item): Book => Book::fromArray($item), readList($this->path));
    }

    public function save(array $books): void
    {
        $rows = array_map(fn(Book $book): array => $book->toArray(), $books);
        $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (file_put_contents($this->path, $json . "\n") === false) {
            throw new RuntimeException("Cannot save the books to $this->path");
        }
    }
}
