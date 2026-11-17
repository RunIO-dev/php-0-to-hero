<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';
require_once subject(__DIR__, 'books.php');

final class BooksTest extends TestCase
{
    private PdoBooks $books;

    protected function setUp(): void
    {
        $this->books = new PdoBooks(testLibrary());
    }

    public function testANewBookGetsItsId(): void
    {
        $saved = $this->books->save(new Book('Kitchen', 'Banana Yoshimoto', 1988, 2));

        self::assertEquals(new Book('Kitchen', 'Banana Yoshimoto', 1988, 2, 6), $saved);
        self::assertEquals($saved, $this->books->find(6));
    }

    public function testAStoredBookIsUpdated(): void
    {
        $sula = $this->books->find(3);
        self::assertNotNull($sula);

        $this->books->save(new Book($sula->title, $sula->author, $sula->year, 1, $sula->id));

        self::assertEquals(new Book('Sula', 'Toni Morrison', 1973, 1, 3), $this->books->find(3));
        self::assertCount(5, $this->books->all());
    }

    public function testABookThatIsntStoredCantBeUpdated(): void
    {
        $this->expectException(OutOfBoundsException::class);

        $this->books->save(new Book('Dune', 'Frank Herbert', 1965, 1, 99));
    }

    public function testAllInOrderOfTitle(): void
    {
        self::assertSame(
            ['At Swim-Two-Birds', 'Beloved', 'Kindred', 'Sula', 'Things Fall Apart'],
            array_map(static fn(Book $book): string => $book->title, $this->books->all()),
        );
        self::assertSame(1, $this->books->all()[4]->id);
    }

    public function testADeletedBookIsGone(): void
    {
        $kindred = $this->books->find(2);
        self::assertNotNull($kindred);

        $this->books->delete($kindred);

        self::assertNull($this->books->find(2));
    }

    public function testANewBookCantBeDeleted(): void
    {
        $this->expectException(LogicException::class);

        $this->books->delete(new Book('Dune', 'Frank Herbert', 1965));
    }
}
