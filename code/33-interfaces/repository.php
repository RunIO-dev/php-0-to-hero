<?php

declare(strict_types=1);

final readonly class Book
{
    public function __construct(public string $title) {}
}

// Where the books are kept is hidden behind the interface.
interface BookRepository
{
    /** @return list<Book> */
    public function all(): array;
}

final readonly class InMemoryBookRepository implements BookRepository
{
    /** @param list<Book> $books */
    public function __construct(private array $books) {}

    public function all(): array
    {
        return $this->books;
    }
}

function titleList(BookRepository $repository): string
{
    return implode(', ', array_map(fn(Book $book): string => $book->title, $repository->all()));
}

$repository = new InMemoryBookRepository([new Book('Kindred'), new Book('Kitchen')]);

echo titleList($repository), "\n";
