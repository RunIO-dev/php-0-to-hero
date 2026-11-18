<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'language.php');

final class LanguageTest extends TestCase
{
    private const array OTHERS = ['de', 'ar'];

    public function testTheLanguageIsTheStartOfTheAddress(): void
    {
        self::assertSame(['de', '/books'], language('/de/books', self::OTHERS));
        self::assertSame(['ar', '/book?title=Kindred'], language('/ar/book?title=Kindred', self::OTHERS));
    }

    public function testTheLanguageAloneIsItsFirstPage(): void
    {
        self::assertSame(['ar', '/'], language('/ar', self::OTHERS));
        self::assertSame(['de', '/'], language('/de/', self::OTHERS));
    }

    public function testEverythingElseIsEnglish(): void
    {
        self::assertSame(['en', '/desk'], language('/desk', self::OTHERS));
        self::assertSame(['en', '/'], language('/', self::OTHERS));
        self::assertSame(['en', '/fr/desk'], language('/fr/desk', self::OTHERS), 'Not one of the languages');
        self::assertSame(['en', '/details'], language('/details', self::OTHERS), 'Starts with /de, not with /de/');
    }
}
