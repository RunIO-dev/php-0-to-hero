<?php

declare(strict_types=1);

// Exercise 77, intermediate: tests for a Doctrine repository.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/books.php';

final class BookFinderTest extends TestCase
{
    private BookFinder $finder;

    protected function setUp(): void
    {
        $entities = testDatabase();
        foreach ([
            new Book('Kindred', 'Octavia E. Butler', 2),
            new Book('Dawn', 'Octavia E. Butler', 0),
            new Book('Parable of the Sower', 'Octavia E. Butler', 1),
            new Book('Sula', 'Toni Morrison', 1),
            new Book('Ninety-nine percent', '99% Invisible', 1),
        ] as $book) {
            $entities->persist($book);
        }
        $entities->flush();
        $entities->clear(); // the finder reads from the database, not from the objects above
        $this->finder = new BookFinder($entities);
    }

    public function testAPartOfTheAuthorsNameFindsTheirBooks(): void
    {
        self::assertSame(['Dawn', 'Kindred', 'Parable of the Sower'], self::titles($this->finder->page('butler', false, 1, 10)));
    }

    public function testAnEmptyAuthorFiltersNothing(): void
    {
        self::assertSame(5, $this->finder->count('', false));
    }

    public function testAPercentSignIsALetter(): void
    {
        self::assertSame(['Ninety-nine percent'], self::titles($this->finder->page('%', false, 1, 10)), 'Not every author');
    }

    public function testOnlyBooksWithACopyLeft(): void
    {
        self::assertSame(['Kindred', 'Parable of the Sower'], self::titles($this->finder->page('butler', true, 1, 10)));
        self::assertSame(2, $this->finder->count('butler', true));
    }

    public function testThePagesSplitTheListInOrder(): void
    {
        self::assertSame(['Dawn', 'Kindred'], self::titles($this->finder->page('', false, 1, 2)));
        self::assertSame(['Ninety-nine percent', 'Parable of the Sower'], self::titles($this->finder->page('', false, 2, 2)));
        self::assertSame(['Sula'], self::titles($this->finder->page('', false, 3, 2)));
        self::assertSame([], $this->finder->page('', false, 4, 2));
    }

    public function testTheCountIsOfAllPages(): void
    {
        self::assertSame(3, $this->finder->count('BUTLER', false));
    }

    public function testPageZeroIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->finder->page('', false, 0, 10);
    }

    public function testAnEmptyPageSizeIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->finder->page('', false, 1, 0);
    }

    /**
     * @param list<Book> $books
     * @return list<string>
     */
    private static function titles(array $books): array
    {
        return array_map(static fn(Book $book): string => $book->title, $books);
    }
}
