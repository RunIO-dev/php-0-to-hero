<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ClockTest extends TestCase
{
    public function testAFixedClock(): void
    {
        require_once subject(__DIR__, 'clock.php');

        $clock = new FixedClock(new DateTimeImmutable('2026-11-16 15:42:10'));

        self::assertInstanceOf(Clock::class, $clock);
        self::assertSame('2026-11-16 15:42:10', $clock->now()->format('Y-m-d H:i:s'));
        self::assertSame('2026-11-16 15:42:10', $clock->now()->format('Y-m-d H:i:s'));
    }

    public function testTheDueDate(): void
    {
        require_once subject(__DIR__, 'clock.php');

        $clock = new FixedClock(new DateTimeImmutable('2026-11-16 15:42:10'));

        self::assertSame('2026-11-30 00:00:00', dueDate($clock, 14)->format('Y-m-d H:i:s'));
        self::assertSame('2026-12-14 00:00:00', dueDate($clock, 28)->format('Y-m-d H:i:s'));
    }

    public function testTheSystemClock(): void
    {
        require_once subject(__DIR__, 'clock.php');

        $before = new DateTimeImmutable();
        $now = (new SystemClock())->now();
        $after = new DateTimeImmutable();

        self::assertInstanceOf(Clock::class, new SystemClock());
        self::assertTrue($before <= $now && $now <= $after);
    }
}
