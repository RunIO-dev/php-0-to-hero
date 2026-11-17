<?php

declare(strict_types=1);

// Exercise 41, beginner: your first tests. A solution.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fine.php';

final class FineTest extends TestCase
{
    public function testTwentyCentsForEachDayLate(): void
    {
        self::assertSame(60, fine(3));
    }

    public function testAtMostFiveHundredCents(): void
    {
        self::assertSame(500, fine(40));
    }

    public function testStaffPayNothing(): void
    {
        self::assertSame(0, fine(3, isStaff: true));
    }

    public function testNothingWhenEarly(): void
    {
        self::assertSame(0, fine(-2));
    }
}
