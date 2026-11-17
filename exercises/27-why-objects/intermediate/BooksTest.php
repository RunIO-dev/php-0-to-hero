<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BooksTest extends TestCase
{
    public function testRowsBecomeBooks(): void
    {
        require_once subject(__DIR__, 'books.php');

        $books = toBooks([['title' => 'Kindred', 'year' => 1979], ['title' => 'Kitchen', 'year' => 1988]]);

        self::assertCount(2, $books);
        self::assertInstanceOf(Book::class, $books[0]);
        self::assertSame('Kindred', $books[0]->title);
        self::assertSame(1988, $books[1]->year);
        self::assertSame([], toBooks([]));
    }

    public function testTheOldestBook(): void
    {
        require_once subject(__DIR__, 'books.php');

        $books = toBooks([
            ['title' => 'Kindred', 'year' => 1979],
            ['title' => 'Things Fall Apart', 'year' => 1958],
            ['title' => 'Season of Migration to the North', 'year' => 1966],
            ['title' => 'The Leopard', 'year' => 1958],
        ]);

        self::assertSame($books[1], oldest($books));
    }
}
