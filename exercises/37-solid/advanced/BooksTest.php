<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BooksTest extends TestCase
{
    public function testTheInterfaces(): void
    {
        require_once subject(__DIR__, 'books.php');

        self::assertTrue(interface_exists('BookReader') && interface_exists('BookWriter'));
        self::assertFalse(interface_exists('BookRepository', false), 'Remove BookRepository.');
        self::assertSame(['BookReader', 'BookWriter'], self::sorted(class_implements(MemoryBooks::class)));
        self::assertSame(['BookReader'], self::sorted(class_implements(Archive::class)));
        self::assertFalse(method_exists(Archive::class, 'save'), 'Archive should have no save().');
    }

    public function testTheCatalogOnlyReads(): void
    {
        require_once subject(__DIR__, 'books.php');

        $type = (string) (new ReflectionMethod(Catalog::class, '__construct'))->getParameters()[0]->getType();
        self::assertSame('BookReader', $type);
        self::assertSame('The Epic of Gilgamesh, The Tale of Genji', (new Catalog(new Archive()))->text());
    }

    public function testMemoryBooks(): void
    {
        require_once subject(__DIR__, 'books.php');

        $books = new MemoryBooks(['Kindred']);
        $books->save(['Kindred', 'Sula']);

        self::assertSame(['Kindred', 'Sula'], $books->titles());
        self::assertSame('Kindred, Sula', (new Catalog($books))->text());
    }

    /**
     * @param array<string, string>|false $names
     * @return list<string>
     */
    private static function sorted(array|false $names): array
    {
        $names = array_values($names === false ? [] : $names);
        sort($names);

        return $names;
    }
}
