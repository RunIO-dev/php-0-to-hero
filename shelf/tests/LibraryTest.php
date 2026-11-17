<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\Library;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\NoCopyLeft;
use Shelf\Role;

final class LibraryTest extends TestCase
{
    private InMemoryBookRepository $books;
    private Library $library;

    protected function setUp(): void
    {
        $this->books = new InMemoryBookRepository([
            new Book('Kindred', 'Octavia E. Butler', 1979, copies: 1),
            new Book('Things Fall Apart', 'Chinua Achebe', 1958, copies: 2),
        ]);
        $this->library = new Library($this->books, [new Member('Amira', Role::Reader)]);
    }

    public function testLendingSavesOneCopyLess(): void
    {
        $this->library->lend('things fall apart', 'amira', new DateTimeImmutable('2026-03-02'));

        $book = $this->library->books()[1];
        self::assertSame('Things Fall Apart', $book->title);
        self::assertSame(1, $book->copies);
    }

    public function testTheLastCopyCanBeLentOnlyOnce(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $this->expectException(NoCopyLeft::class);
        $this->expectExceptionMessage('All copies of "Kindred" are on loan');

        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
    }

    public function testAnUnknownBookCantBeLent(): void
    {
        $this->expectException(BookNotFound::class);
        $this->expectExceptionMessage('There is no book titled "Dune"');

        $this->library->lend('Dune', 'Amira', new DateTimeImmutable('2026-03-02'));
    }

    public function testAnUnknownMemberCantBorrow(): void
    {
        $this->expectException(MemberNotFound::class);

        $this->library->lend('Kindred', 'Nobody', new DateTimeImmutable('2026-03-02'));
    }

    public function testATitleCanBeAddedOnlyOnceInAnyCase(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Kindred" is already on the shelf');

        $this->library->add(['  KINDRED ', 'Octavia E. Butler', '1979']);
    }

    public function testAnAddedBookIsSavedInOrder(): void
    {
        $this->library->add(['Beloved', 'Toni Morrison', '1987']);

        self::assertSame(['Beloved', 'Kindred', 'Things Fall Apart'], array_column($this->library->books(), 'title'));
    }
}
