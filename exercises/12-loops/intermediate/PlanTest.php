<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlanTest extends TestCase
{
    /** @return array<string, array{string, string, string}> */
    public static function books(): array
    {
        return [
            'the example' => ['60', '25', "Day 1: 25 pages, 35 left\nDay 2: 25 pages, 10 left\nDay 3: 10 pages, 0 left\n"],
            'even days' => ['50', '25', "Day 1: 25 pages, 25 left\nDay 2: 25 pages, 0 left\n"],
            'one day' => ['12', '30', "Day 1: 12 pages, 0 left\n"],
        ];
    }

    #[DataProvider('books')]
    public function testPrintsOneLinePerDay(string $pages, string $pagesPerDay, string $expected): void
    {
        self::assertSame($expected, output_with(subject(__DIR__, 'plan.php'), ['pages' => $pages, 'pagesPerDay' => $pagesPerDay]));
    }
}
