<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Fine.php');

final class FineTest extends TestCase
{
    /** @return iterable<string, array{string, string, int}> due, returned, cents */
    public static function returns(): iterable
    {
        yield 'early' => ['2026-03-16', '2026-03-10 12:00', 0];
        yield 'late on the due date' => ['2026-03-16', '2026-03-16 23:59', 0];
        yield 'a day late' => ['2026-03-16', '2026-03-17 00:01', 25];
        yield 'ten days late' => ['2026-03-16', '2026-03-26 09:00', 250];
        yield 'across summer time' => ['2026-03-28', '2026-03-30 10:00', 50];
        yield 'two months late, at most ten euros' => ['2026-03-16', '2026-05-16', 1000];
    }

    #[DataProvider('returns')]
    public function testAFineIsCountedInDays(string $due, string $returned, int $cents): void
    {
        $berlin = new DateTimeZone('Europe/Berlin');

        self::assertSame($cents, Fine::for(new DateTimeImmutable($due, $berlin), new DateTimeImmutable($returned, $berlin)));
    }

    public function testAFineReadsAsEuros(): void
    {
        self::assertSame(['2.50 €', '0.05 €', '0.00 €', '10.00 €'], array_map(Fine::format(...), [250, 5, 0, 1000]));
    }
}
