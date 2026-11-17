<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use PDOException;
use Shelf\Book;
use Shelf\BookRepository;
use Shelf\Isbn;
use Shelf\PdoBookRepository;
use Shelf\Tests\BookRepositoryContract;

use function Shelf\openDatabase;

/**
 * The books in a real SQLite database, made new for every test, in memory, by the migrations
 * (chapter 60): the contract's tests, and the rules that only the database checks.
 */
final class PdoBookRepositoryTest extends BookRepositoryContract
{
    protected function repository(Book ...$books): BookRepository
    {
        $repository = new PdoBookRepository(openDatabase(':memory:'));
        foreach ($books as $book) {
            $repository->add($book);
        }

        return $repository;
    }

    public function testEveryColumnComesBack(): void
    {
        $book = new Book('Kindred', 'Octavia E. Butler', 1979, 3, new Isbn('9780807083697'));

        self::assertEquals($book, $this->repository($book)->find('Kindred'));
    }

    public function testTheDatabaseRefusesATitleTwice(): void
    {
        $books = $this->repository(new Book('Kindred', 'Octavia E. Butler', 1979));

        $this->expectException(PDOException::class);
        $this->expectExceptionMessage('UNIQUE constraint failed: books.title');

        $books->add(new Book('KINDRED', 'Someone', 2000));
    }
}
