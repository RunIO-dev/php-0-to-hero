<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'NightlyReset.php');

final class NightlyResetTest extends TestCase
{
    public function testTheNextResetIsTonightOrTomorrowNight(): void
    {
        $reset = new NightlyReset();

        self::assertSame('2026-03-16 03:00 UTC', $reset->next(new DateTimeImmutable('2026-03-16 02:59 UTC'))->format('Y-m-d H:i T'));
        self::assertSame('2026-03-17 03:00 UTC', $reset->next(new DateTimeImmutable('2026-03-16 03:00 UTC'))->format('Y-m-d H:i T'), 'This one is over');
        self::assertSame('2026-03-17 03:00 UTC', $reset->next(new DateTimeImmutable('2026-03-16 22:00 UTC'))->format('Y-m-d H:i T'));
    }

    public function testNowMayBeInAnyZone(): void
    {
        $berlin = new DateTimeImmutable('2026-07-01 04:30', new DateTimeZone('Europe/Berlin')); // 02:30 UTC

        self::assertSame('2026-07-01 03:00 UTC', new NightlyReset()->next($berlin)->format('Y-m-d H:i T'));
        self::assertSame('in 30 minutes', new NightlyReset()->until($berlin));
    }

    public function testUntilInWords(): void
    {
        $reset = new NightlyReset();

        self::assertSame('in 5 hours', $reset->until(new DateTimeImmutable('2026-03-16 21:30 UTC')));
        self::assertSame('in 1 hour', $reset->until(new DateTimeImmutable('2026-03-16 01:59 UTC')));
        self::assertSame('in 45 minutes', $reset->until(new DateTimeImmutable('2026-03-16 02:15 UTC')));
        self::assertSame('in 1 minute', $reset->until(new DateTimeImmutable('2026-03-16 02:59:30 UTC')));
    }

    public function testTheNightTheClocksGoForwardIsShorter(): void
    {
        $reset = new NightlyReset('03:00', 'Europe/Berlin');
        $noon = new DateTimeImmutable('2026-03-28 12:00', new DateTimeZone('Europe/Berlin'));

        self::assertSame('2026-03-29 03:00 CEST', $reset->next($noon)->format('Y-m-d H:i T'));
        self::assertSame('in 14 hours', $reset->until($noon), '15 on the clock, 14 in fact');
    }
}
