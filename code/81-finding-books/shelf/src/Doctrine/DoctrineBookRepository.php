<?php

declare(strict_types=1);

namespace Shelf\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\BookQuery;
use Shelf\BookRepository;

/**
 * The books, through Doctrine (chapter 74), in place of PdoBookRepository. Doctrine writes the SQL;
 * where a query needs more than "find by", it is DQL, which names classes and properties instead of
 * tables and columns. The title's column compares in any case (NOCASE), as before.
 */
final readonly class DoctrineBookRepository implements BookRepository
{
    public function __construct(private EntityManagerInterface $entities) {}

    public function all(): array
    {
        return $this->entities->getRepository(Book::class)->findBy([], ['id' => 'ASC']);
    }

    public function page(BookQuery $query, int $offset, int $limit): array
    {
        $builder = $this->matching($query)->setFirstResult($offset)->setMaxResults($limit);
        foreach ($query->sort->fields() as $field => $direction) {
            $builder->addOrderBy("b.$field", $direction);
        }

        /** @var list<Book> */
        return $builder->getQuery()->getResult();
    }

    public function count(BookQuery $query): int
    {
        return (int) $this->matching($query)->select('COUNT(b.id)')->getQuery()->getSingleScalarResult();
    }

    public function find(string $title): ?Book
    {
        return $this->entities->getRepository(Book::class)->findOneBy(['title' => trim($title)]);
    }

    public function add(Book $book): void
    {
        $this->entities->persist($book);
        $this->entities->flush();
    }

    public function update(Book $book): void
    {
        if (!$this->entities->contains($book)) {
            throw new BookNotFound("There is no book titled \"$book->title\"");
        }
        $this->entities->flush(); // Doctrine finds what changed, and writes only that
    }

    public function remove(string $title): void
    {
        $this->entities->remove($this->find($title) ?? throw new BookNotFound("There is no book titled \"$title\""));
        $this->entities->flush();
    }

    /**
     * LIKE finds part of a name; % and _ that a visitor types are taken as letters, not as wildcards.
     * Chapter 81: the search text in the title or the author, or the digits of the ISBN.
     */
    private function matching(BookQuery $query): QueryBuilder
    {
        $builder = $this->entities->createQueryBuilder()->select('b')->from(Book::class, 'b');
        if ($query->author !== '') {
            $builder->andWhere("LOWER(b.author) LIKE :author ESCAPE '\\'")->setParameter('author', self::pattern($query->author));
        }
        if ($query->text !== '') {
            $either = $builder->expr()->orX("LOWER(b.title) LIKE :text ESCAPE '\\'", "LOWER(b.author) LIKE :text ESCAPE '\\'");
            $builder->setParameter('text', self::pattern($query->text));
            $digits = BookQuery::isbnDigits($query->text);
            if ($digits !== '') {
                $either->add('b.isbn LIKE :digits');
                $builder->setParameter('digits', "%$digits%");
            }
            $builder->andWhere($either);
        }
        if ($query->availableOnly) {
            $builder->andWhere('b.copies > 0');
        }

        return $builder;
    }

    /** "%part%" for LIKE, in lower case, with the wildcards of the text itself escaped. */
    private static function pattern(string $text): string
    {
        return '%' . addcslashes(mb_strtolower($text), '%_\\') . '%';
    }
}
