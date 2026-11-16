<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReadingDaysTest extends TestCase
{
    /** @return array<string, array{int, int, int}> */
    public static function books(): array
    {
        return [
            'exact' => [300, 30, 10],
            'a part of a day' => [320, 30, 11],
            'one page' => [1, 30, 1],
            'a short book' => [90, 100, 1],
        ];
    }

    #[DataProvider('books')]
    public function testCountsWholeDays(int $pages, int $pagesPerDay, int $days): void
    {
        require_once subject(__DIR__, 'reading-days.php');

        self::assertSame($days, readingDays($pages, $pagesPerDay));
    }
}
