<?php

declare(strict_types=1);

final readonly class Book
{
    public function __construct(public string $title) {}
}

class JsonBookFile
{
    public function __construct(protected string $path) {}

    /** @return list<Book> */
    public function all(): array
    {
        return [];
    }

    public function delete(): void
    {
        unlink($this->path);
    }
}

// ✗ A library is not a file. It inherits delete(), and every caller can now delete the books.
final class Library extends JsonBookFile
{
    /** @return list<string> */
    public function titles(): array
    {
        return array_map(fn(Book $book): string => $book->title, $this->all());
    }
}
