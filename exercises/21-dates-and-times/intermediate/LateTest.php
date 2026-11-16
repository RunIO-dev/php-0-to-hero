<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LateTest extends TestCase
{
    /** @return array<string, array{string, string, int}> */
    public static function returns(): array
    {
        return [
            'three days late' => ['2026-12-01', '2026-12-04', 3],
            'on the day' => ['2026-12-01', '2026-12-01', 0],
            'early' => ['2026-12-01', '2026-11-28', 0],
            'over the new year' => ['2026-12-30', '2027-01-02', 3],
            'a leap year' => ['2028-02-27', '2028-03-01', 3],
        ];
    }

    #[DataProvider('returns')]
    public function testDaysLate(string $due, string $returned, int $expected): void
    {
        require_once subject(__DIR__, 'late.php');

        self::assertSame($expected, daysLate(new DateTimeImmutable($due), new DateTimeImmutable($returned)));
    }
}
