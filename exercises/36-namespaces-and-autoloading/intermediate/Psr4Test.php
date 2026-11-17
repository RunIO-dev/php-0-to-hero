<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Psr4Test extends TestCase
{
    public function testPaths(): void
    {
        require_once subject(__DIR__, 'psr4.php');

        self::assertSame('/app/src/Book.php', classFile('Shelf\Book', 'Shelf\\', '/app/src'));
        self::assertSame('/app/src/Storage/Json.php', classFile('Shelf\Storage\Json', 'Shelf\\', '/app/src'));
        self::assertSame('/home/ana/lib/Money.php', classFile('Acme\Shop\Money', 'Acme\Shop\\', '/home/ana/lib'));
    }

    public function testOtherPrefixes(): void
    {
        require_once subject(__DIR__, 'psr4.php');

        self::assertNull(classFile('Other\Book', 'Shelf\\', '/app/src'));
        self::assertNull(classFile('ShelfX\Book', 'Shelf\\', '/app/src'));
        self::assertNull(classFile('Book', 'Shelf\\', '/app/src'));
    }
}
