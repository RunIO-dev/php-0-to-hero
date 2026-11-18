<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Pickup.php');

final class PickupTest extends TestCase
{
    /** @return iterable<string, array{string, string}> */
    public static function days(): iterable
    {
        yield 'a Monday' => ['2026-03-02 17:45', '2026-03-09'];
        yield 'into the next month' => ['2026-03-28 09:00', '2026-04-04'];
        yield 'a week later is a Sunday' => ['2026-03-01 12:00', '2026-03-09'];
        yield 'across the year' => ['2026-12-27 10:00', '2027-01-04'];
    }

    #[DataProvider('days')]
    public function testTheLastDayIsAWeekLaterButNeverASunday(string $readyOn, string $lastDay): void
    {
        $last = Pickup::lastDay(new DateTimeImmutable($readyOn));

        self::assertSame("$lastDay 00:00", $last->format('Y-m-d H:i'));
    }

    public function testTheLastDayCounts(): void
    {
        $last = new DateTimeImmutable('2026-03-09');

        self::assertFalse(Pickup::expired($last, new DateTimeImmutable('2026-03-09 23:59')));
        self::assertTrue(Pickup::expired($last, new DateTimeImmutable('2026-03-10 00:00')));
        self::assertFalse(Pickup::expired($last, new DateTimeImmutable('2026-03-02')));
    }

    public function testDaysLeft(): void
    {
        $last = new DateTimeImmutable('2026-03-09');

        self::assertSame(7, Pickup::daysLeft($last, new DateTimeImmutable('2026-03-02 18:00')));
        self::assertSame(1, Pickup::daysLeft($last, new DateTimeImmutable('2026-03-08 23:00')));
        self::assertSame(0, Pickup::daysLeft($last, new DateTimeImmutable('2026-03-09 08:00')));
        self::assertSame(0, Pickup::daysLeft($last, new DateTimeImmutable('2026-03-12')));
    }
}
