<?php

declare(strict_types=1);

namespace Shelf\Tests;

use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\BookQuery;
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

        self::assertSame(new Book('Kindred', 'Octavia E. Butler', 1979, 2)->toArray(), $books->find('KINDRED')?->toArray());
        self::assertNull($books->find('Dune'));
    }

    public function testAllInTheOrderTheyWereAdded(): void
    {
        $books = $this->repository(new Book('Sula', 'Toni Morrison', 1973), new Book('Kindred', 'Octavia E. Butler', 1979));
        $books->add(new Book('Beloved', 'Toni Morrison', 1987));

        self::assertSame(['Sula', 'Kindred', 'Beloved'], array_column($books->all(), 'title'));
    }

    public function testAChangeIsStored(): void
    {
        $books = $this->repository(new Book('Kindred', 'Octavia E. Butler', 1979, 2));
        $kindred = $books->find('Kindred');
        self::assertNotNull($kindred);

        $kindred->changeCopies(5);
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

    public function testAPageIsSortedByTitle(): void
    {
        $books = $this->repository(...self::shelf());

        self::assertSame(['Beloved', 'Dawn', 'kindred'], array_column($books->page(new BookQuery(), 0, 3), 'title'));
        self::assertSame(['Sula'], array_column($books->page(new BookQuery(), 3, 3), 'title'));
        self::assertSame([], $books->page(new BookQuery(), 9, 3));
        self::assertSame(4, $books->count(new BookQuery()));
    }

    public function testAQueryFiltersByAuthorInAnyCaseAndByCopies(): void
    {
        $books = $this->repository(...self::shelf());

        self::assertSame(['Dawn', 'kindred'], array_column($books->page(new BookQuery('BUTLER'), 0, 10), 'title'));
        self::assertSame(['Dawn'], array_column($books->page(new BookQuery('butler', availableOnly: true), 0, 10), 'title'));
        self::assertSame(3, $books->count(new BookQuery(availableOnly: true)));
        self::assertSame(0, $books->count(new BookQuery('%')), 'A % is a letter, not a wildcard');
    }

    /** @return list<Book> */
    private static function shelf(): array
    {
        return [new Book('Sula', 'Toni Morrison', 1973), new Book('kindred', 'Octavia E. Butler', 1979, 0),
            new Book('Beloved', 'Toni Morrison', 1987), new Book('Dawn', 'Octavia E. Butler', 1987)];
    }
}
