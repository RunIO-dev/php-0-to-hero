<?php

declare(strict_types=1);

// The book list of exercise 74 (advanced): a Doctrine entity, and a class that finds books a page at a
// time. testDatabase() makes an empty SQLite database in memory with the books table, for the tests.

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\SchemaTool;

#[ORM\Entity]
#[ORM\Table(name: 'books')]
final class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    public function __construct(
        #[ORM\Column]
        public readonly string $title,
        #[ORM\Column]
        public readonly string $author,
        #[ORM\Column]
        public readonly int $copies,
    ) {}
}

/**
 * Books sorted by title, filtered by part of the author's name (in any case; a % or _ typed in the
 * filter is a letter, not a wildcard; an empty author filters nothing) and by whether a copy is on the shelf.
 */
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

    /** How many books match, on all pages together. */
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

/** An empty database in memory, with the books table: a new one for every call. */
function testDatabase(): EntityManager
{
    $config = ORMSetup::createAttributeMetadataConfig([], isDevMode: true);
    $config->enableNativeLazyObjects(true);
    $entities = new EntityManager(DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config), $config);
    new SchemaTool($entities)->createSchema([$entities->getClassMetadata(Book::class)]);

    return $entities;
}
