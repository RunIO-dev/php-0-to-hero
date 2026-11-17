<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
    public function testFoundAndNotFound(): void
    {
        [$found, $missing, $none] = responses_of(dirname(subject(__DIR__, 'book.php')), "GET /book.php?title=Sula\n###\nGET /book.php?title=Dune\n###\nGET /book.php\n");

        self::assertSame(200, $found['status']);
        self::assertSame("Sula, Toni Morrison, 1973\n", $found['body']);
        self::assertSame(404, $missing['status']);
        self::assertSame("No such book\n", $missing['body']);
        self::assertSame(404, $none['status']);
    }
}
