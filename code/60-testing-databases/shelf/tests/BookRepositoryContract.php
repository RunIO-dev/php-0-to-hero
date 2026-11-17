<?php

declare(strict_types=1);

namespace Shelf\Tests;

use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\BookRepository;

/**
 * What every BookRepository must do (chapter 60). One test class per repository extends this:
 * the one in memory, which the other tests use, and the one in the database. If the two ever
 * behave differently, these tests show it, so a passing LibraryTest means something.
 */
abstract class BookRepositoryContract extends TestCase
{
    /** A new, empty repository with these books in it. */
    abstract protected function repository(Book ...$books): BookRepository;

    public function testABookCanBeFoundInAnyCase(): void
    {
        $books = $this->repository(new Book('Kindred', 'Octavia E. Butler', 1979, 2));

        self::assertEquals(new Book('Kindred', 'Octavia E. Butler', 1979, 2), $books->find('KINDRED'));
        self::assertNull($books->find('Dune'));
    }

    public function testAllInTheOrderTheyWereAdded(): void
    {
        $books = $this->repository(new Book('Sula', 'Toni Morrison', 1973), new Book('Kindred', 'Octavia E. Butler', 1979));
        $books->add(new Book('Beloved', 'Toni Morrison', 1987));

        self::assertSame(['Sula', 'Kindred', 'Beloved'], array_column($books->all(), 'title'));
    }

    public function testAChangeCountsOnlyOnceItIsStored(): void
    {
        $books = $this->repository(new Book('Kindred', 'Octavia E. Butler', 1979, 2));
        $kindred = $books->find('Kindred');
        self::assertNotNull($kindred);

        $kindred->changeCopies(5);
        self::assertSame(2, $books->find('Kindred')?->copies);

        $books->update($kindred);
        self::assertSame(5, $books->find('Kindred')?->copies);
    }

    public function testARemovedBookIsGone(): void
    {
        $books = $this->repository(new Book('Kindred', 'Octavia E. Butler', 1979), new Book('Sula', 'Toni Morrison', 1973));

        $books->remove('kindred');

        self::assertSame(['Sula'], array_column($books->all(), 'title'));
    }

    public function testAnUnknownBookCantBeUpdated(): void
    {
        $this->expectException(BookNotFound::class);

        $this->repository()->update(new Book('Dune', 'Frank Herbert', 1965));
    }

    public function testAnUnknownBookCantBeRemoved(): void
    {
        $this->expectException(BookNotFound::class);

        $this->repository()->remove('Dune');
    }
}
