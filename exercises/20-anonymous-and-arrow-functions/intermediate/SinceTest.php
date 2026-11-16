<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SinceTest extends TestCase
{
    private const BOOKS = [
        ['title' => 'Things Fall Apart', 'year' => 1958],
        ['title' => 'Kindred', 'year' => 1979],
        ['title' => 'Kitchen', 'year' => 1988],
        ['title' => 'Parable of the Sower', 'year' => 1993],
    ];

    /** @return array<string, array{int, list<string>}> */
    public static function years(): array
    {
        return [
            'the newer two' => [1980, ['Kitchen', 'Parable of the Sower']],
            'the year itself counts' => [1979, ['Kindred', 'Kitchen', 'Parable of the Sower']],
            'all' => [1900, ['Things Fall Apart', 'Kindred', 'Kitchen', 'Parable of the Sower']],
            'none' => [2000, []],
        ];
    }

    /** @param list<string> $expected */
    #[DataProvider('years')]
    public function testTitlesSince(int $year, array $expected): void
    {
        require_once subject(__DIR__, 'since.php');

        // assertSame also compares the keys: they must be 0, 1, 2 ...
        self::assertSame($expected, titlesSince(self::BOOKS, $year));
    }
}
