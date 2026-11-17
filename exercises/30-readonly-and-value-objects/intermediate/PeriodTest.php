<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class PeriodTest extends TestCase
{
    public function testDaysAndContains(): void
    {
        require_once subject(__DIR__, 'period.php');

        $period = new LoanPeriod(new DateTimeImmutable('2026-11-16'), new DateTimeImmutable('2026-11-30'));

        self::assertSame(14, $period->days());
        self::assertTrue($period->contains(new DateTimeImmutable('2026-11-16')));
        self::assertTrue($period->contains(new DateTimeImmutable('2026-11-30')));
        self::assertFalse($period->contains(new DateTimeImmutable('2026-12-01')));
        self::assertFalse($period->contains(new DateTimeImmutable('2026-11-15')));
    }

    public function testExtendingGivesANewPeriod(): void
    {
        require_once subject(__DIR__, 'period.php');

        $period = new LoanPeriod(new DateTimeImmutable('2026-11-16'), new DateTimeImmutable('2026-11-30'));
        $longer = $period->extendedBy(7);

        self::assertSame('2026-12-07', $longer->until->format('Y-m-d'));
        self::assertSame('2026-11-16', $longer->from->format('Y-m-d'));
        self::assertSame(21, $longer->days());
        self::assertSame(14, $period->days());
    }

    public function testItMustEndAfterItStarts(): void
    {
        require_once subject(__DIR__, 'period.php');

        $this->expectException(InvalidArgumentException::class);
        new LoanPeriod(new DateTimeImmutable('2026-11-30'), new DateTimeImmutable('2026-11-30'));
    }

    public function testItCantChange(): void
    {
        require_once subject(__DIR__, 'period.php');

        self::assertTrue((new ReflectionClass(LoanPeriod::class))->isReadOnly(), 'Make the class readonly.');
    }
}
