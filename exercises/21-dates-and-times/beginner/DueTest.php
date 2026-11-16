<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DueTest extends TestCase
{
    public function testFourteenDaysByDefault(): void
    {
        require_once subject(__DIR__, 'due.php');

        self::assertSame('2026-12-01', dueDate(new DateTimeImmutable('2026-11-17'))->format('Y-m-d'));
        self::assertSame('2027-01-08', dueDate(new DateTimeImmutable('2026-12-25'))->format('Y-m-d'));
    }

    public function testOtherLengths(): void
    {
        require_once subject(__DIR__, 'due.php');

        self::assertSame('2027-01-01', dueDate(new DateTimeImmutable('2026-12-25'), 7)->format('Y-m-d'));
        self::assertSame('2028-03-01', dueDate(new DateTimeImmutable('2028-02-28'), 2)->format('Y-m-d'));
    }

    public function testTheLendingDayStaysTheSame(): void
    {
        require_once subject(__DIR__, 'due.php');
        $lentOn = new DateTimeImmutable('2026-11-17');
        dueDate($lentOn);

        self::assertSame('2026-11-17', $lentOn->format('Y-m-d'));
    }
}
