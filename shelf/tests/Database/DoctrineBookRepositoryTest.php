<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Shelf\Book;
use Shelf\BookRepository;
use Shelf\Doctrine\DoctrineBookRepository;
use Shelf\Isbn;
use Shelf\Tests\BookRepositoryContract;

/**
 * The books in a real SQLite database, through Doctrine (chapter 74): the contract's tests, and the
 * rules that only the database checks.
 */
final class DoctrineBookRepositoryTest extends BookRepositoryContract
{
    protected function repository(Book ...$books): BookRepository
    {
        $repository = new DoctrineBookRepository(Entities::manager());
        foreach ($books as $book) {
            $repository->add($book);
        }

        return $repository;
    }

    public function testEveryColumnComesBack(): void
    {
        $entities = Entities::manager();
        new DoctrineBookRepository($entities)->add(new Book('Kindred', 'Octavia E. Butler', 1979, 3, new Isbn('9780807083697')));
        $entities->clear(); // forget the object, so the next find reads the row

        $found = new DoctrineBookRepository($entities)->find('Kindred');

        self::assertNotNull($found);
        self::assertSame(['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'year' => 1979, 'copies' => 3, 'isbn' => '9780807083697'], $found->toArray());
    }

    public function testTheSameRowIsTheSameObject(): void
    {
        $books = $this->repository(new Book('Kindred', 'Octavia E. Butler', 1979));

        self::assertSame($books->find('Kindred'), $books->find('KINDRED'));
    }

    public function testTheDatabaseRefusesATitleTwice(): void
    {
        $books = $this->repository(new Book('Kindred', 'Octavia E. Butler', 1979));

        $this->expectException(UniqueConstraintViolationException::class);

        $books->add(new Book('KINDRED', 'Someone', 2000));
    }
}
