<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'params.php');

final class ParamsTest extends TestCase
{
    public function testAValueIsTakenFromThePath(): void
    {
        self::assertSame(['id' => '42'], matchRoute('/books/{id}', '/books/42'));
        self::assertSame(['name' => 'Amira', 'id' => '7'], matchRoute('/members/{name}/loans/{id}', '/members/Amira/loans/7'));
    }

    public function testAPatternWithoutValuesFitsOnlyItself(): void
    {
        self::assertSame([], matchRoute('/books', '/books'));
        self::assertNull(matchRoute('/books', '/members'));
    }

    public function testOtherShapesDontFit(): void
    {
        self::assertNull(matchRoute('/books/{id}', '/books'));
        self::assertNull(matchRoute('/books/{id}', '/books/'));
        self::assertNull(matchRoute('/books/{id}', '/books/42/edit'));
        self::assertNull(matchRoute('/books/{id}', '/members/42'));
    }

    public function testValuesAreDecoded(): void
    {
        self::assertSame(['title' => 'Things Fall Apart'], matchRoute('/books/{title}', '/books/Things%20Fall%20Apart'));
    }

    public function testOtherCharactersMeanThemselves(): void
    {
        self::assertSame([], matchRoute('/a.b', '/a.b'));
        self::assertNull(matchRoute('/a.b', '/axb'));
        self::assertNull(matchRoute('/books/(.*)', '/books/42'));
    }
}
