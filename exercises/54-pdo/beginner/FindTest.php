<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';
require_once subject(__DIR__, 'find.php');

final class FindTest extends TestCase
{
    public function testTheTitlesOfOneAuthorInOrder(): void
    {
        self::assertSame(['Beloved', 'Sula'], titlesBy(testLibrary(), 'Toni Morrison'));
    }

    public function testANameWithAQuote(): void
    {
        self::assertSame(['At Swim-Two-Birds'], titlesBy(testLibrary(), "Flann O'Brien"));
    }

    public function testAnAttackFindsNothing(): void
    {
        self::assertSame([], titlesBy(testLibrary(), "' OR '1'='1"));
    }

}
