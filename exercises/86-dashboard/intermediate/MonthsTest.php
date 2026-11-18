<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'months.php');

final class MonthsTest extends TestCase
{
    public function testTheEmptyMonthsGetZero(): void
    {
        self::assertSame(
            ['2026-01' => 2, '2026-02' => 0, '2026-03' => 2, '2026-04' => 0],
            everyMonth(['2026-01' => 2, '2026-03' => 2], '2026-01', '2026-04'),
        );
    }

    public function testOverTheNewYear(): void
    {
        self::assertSame(['2025-11' => 0, '2025-12' => 4, '2026-01' => 1], everyMonth(['2025-12' => 4, '2026-01' => 1], '2025-11', '2026-01'));
    }

    public function testCountsOutsideTheMonthsAreLeftOut(): void
    {
        self::assertSame(['2026-02' => 0], everyMonth(['2026-01' => 2, '2026-03' => 5], '2026-02', '2026-02'));
    }

    public function testATwelveMonthYearHasTwelve(): void
    {
        self::assertCount(12, everyMonth([], '2026-01', '2026-12'));
    }

    public function testTheMonthsMustBeInOrder(): void
    {
        $this->expectException(InvalidArgumentException::class);

        everyMonth([], '2026-03', '2026-01');
    }
}
