<?php

declare(strict_types=1);

// Chapter 70's list of books, with Doctrine's query builder: a page at a time, sorted by title,
// filtered by part of the author's name (in any case; a % or _ typed in the filter is a letter, not a
// wildcard) and by whether a copy is on the shelf. An empty author filters nothing.

use Catalogue\Book;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

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
        if ($page < 1 || $perPage < 1) {
            throw new InvalidArgumentException('Pages count from 1, and hold at least one book');
        }

        /** @var list<Book> */
        return $this->matching($author, $availableOnly)
            ->orderBy('b.title')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
    }

    public function count(string $author, bool $availableOnly): int
    {
        return (int) $this->matching($author, $availableOnly)->select('COUNT(b.id)')->getQuery()->getSingleScalarResult();
    }

    private function matching(string $author, bool $availableOnly): QueryBuilder
    {
        $query = $this->entities->createQueryBuilder()->select('b')->from(Book::class, 'b');
        if ($author !== '') {
            $query->andWhere("LOWER(b.author) LIKE :author ESCAPE '\\'")
                ->setParameter('author', '%' . addcslashes(mb_strtolower($author), '%_\\') . '%');
        }
        if ($availableOnly) {
            $query->andWhere('b.copies > 0');
        }

        return $query;
    }
}
