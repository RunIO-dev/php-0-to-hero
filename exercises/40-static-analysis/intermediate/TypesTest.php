<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TypesTest extends TestCase
{
    private const array BOOKS = [
        ['title' => 'Kindred', 'year' => 1979],
        ['title' => 'Beloved', 'year' => 1987],
        ['title' => 'Sula', 'year' => 1973],
    ];

    public function testPhpstanAtLevel6FindsNothing(): void
    {
        $analysis = analysis_of(subject(__DIR__, 'types.php'), 6);

        self::assertTrue($analysis['passed'], "PHPStan says:\n" . $analysis['out']);
    }

    public function testTheCodeStillWorks(): void
    {
        require_once subject(__DIR__, 'types.php');

        self::assertSame(['Kindred', 'Beloved', 'Sula'], titles(self::BOOKS));
        self::assertSame(['title' => 'Beloved', 'year' => 1987], newest(self::BOOKS));
        self::assertSame([1970 => 2, 1980 => 1], countByDecade(self::BOOKS));
    }
}
