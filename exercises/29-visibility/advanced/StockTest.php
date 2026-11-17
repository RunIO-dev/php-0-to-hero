<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StockTest extends TestCase
{
    public function testLendAndGiveBack(): void
    {
        require_once subject(__DIR__, 'stock.php');

        $book = new Book('Kindred', 3);
        $book->lend();
        $book->lend();
        $book->giveBack();

        self::assertSame('Kindred', $book->title);
        self::assertSame(2, $book->copies);
        self::assertSame(1, $book->onLoan());
    }

    public function testNoCopyLeft(): void
    {
        require_once subject(__DIR__, 'stock.php');

        $book = new Book('Kitchen', 1);
        $book->lend();
        $this->expectException(RuntimeException::class);
        $book->lend();
    }

    public function testNotMoreThanTheLibraryOwns(): void
    {
        require_once subject(__DIR__, 'stock.php');

        $book = new Book('Sula', 2);
        $this->expectException(RuntimeException::class);
        $book->giveBack();
    }

    public function testAtLeastOneCopy(): void
    {
        require_once subject(__DIR__, 'stock.php');

        $this->expectException(InvalidArgumentException::class);
        new Book('Beloved', 0);
    }

    public function testOnlyTheBookChangesIt(): void
    {
        require_once subject(__DIR__, 'stock.php');

        $book = new Book('Kindred', 3);
        foreach (['copies', 'title'] as $name) {
            $property = new ReflectionProperty(Book::class, $name);
            self::assertTrue($property->isPublic(), "Everyone may read $name.");
            self::assertTrue($property->isPrivateSet() || $property->isReadOnly(), "Only the book may change $name.");
        }
        self::assertSame(3, $book->copies);
    }
}
