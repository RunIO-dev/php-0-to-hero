<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CatalogTest extends TestCase
{
    public function testTheFullNames(): void
    {
        require_once subject(__DIR__, 'catalog.php');

        self::assertTrue(class_exists('Library\Catalog\Book', false), 'The class should be Library\Catalog\Book.');
        self::assertTrue(function_exists('Library\Catalog\dueDate'), 'The function should be Library\Catalog\dueDate.');
        self::assertFalse(class_exists('Book', false), 'There should be no Book in the global namespace.');
    }

    public function testItStillWorks(): void
    {
        require_once subject(__DIR__, 'catalog.php');

        $book = new Library\Catalog\Book('Kindred');
        $due = Library\Catalog\dueDate(new DateTimeImmutable('2026-11-16'));

        self::assertSame('Kindred', $book->title);
        self::assertSame('2026-11-30', $due->format('Y-m-d'));
    }
}
