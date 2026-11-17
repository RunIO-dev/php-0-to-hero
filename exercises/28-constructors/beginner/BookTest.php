<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
    public function testTheConstructorSetsEverything(): void
    {
        require_once subject(__DIR__, 'book.php');

        $book = new Book('Kindred', 'Octavia E. Butler', 1979, 2);

        self::assertSame('Kindred', $book->title);
        self::assertSame('Octavia E. Butler', $book->author);
        self::assertSame(1979, $book->year);
        self::assertSame(2, $book->copies);
    }

    public function testOneCopyByDefault(): void
    {
        require_once subject(__DIR__, 'book.php');

        $book = new Book(year: 1988, title: 'Kitchen', author: 'Banana Yoshimoto');

        self::assertSame(1, $book->copies);
        self::assertSame('Kitchen (1988)', $book->label());
    }
}
