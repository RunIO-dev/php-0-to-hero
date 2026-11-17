<?php

declare(strict_types=1);

// Chapter 70's list of books, with Doctrine's query builder: a page at a time, sorted by title,
// filtered by part of the author's name (in any case; a % or _ typed in the filter is a letter, not a
// wildcard) and by whether a copy is on the shelf. An empty author filters nothing.

use Catalogue\Book;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BookFinder
{
    public function __construct(private EntityManagerInterface $entities) {}

    /**
     * Page $page (from 1) of the matching books, $perPage on each. A page below 1, or fewer than one book per page: InvalidArgumentException.
     *
     * @return list<Book>
     */
    public function page(string $author, bool $availableOnly, int $page, int $perPage): array
    {
        // TODO: the filters, the order, and only one page
        return array_values($this->entities->getRepository(Book::class)->findAll());
    }

    public function count(string $author, bool $availableOnly): int
    {
        return 0; // TODO
    }
}
