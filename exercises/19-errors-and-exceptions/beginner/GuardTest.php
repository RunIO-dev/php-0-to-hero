<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GuardTest extends TestCase
{
    public function testStillWorks(): void
    {
        require_once subject(__DIR__, 'guard.php');

        self::assertSame(23, pagesPerDay(320, 14));
        self::assertSame(320, pagesPerDay(320, 1));
    }

    public function testRefusesZeroDays(): void
    {
        require_once subject(__DIR__, 'guard.php');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Reading takes at least 1 day, not 0');
        pagesPerDay(320, 0);
    }

    public function testRefusesNegativeDays(): void
    {
        require_once subject(__DIR__, 'guard.php');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not -3');
        pagesPerDay(320, -3);
    }
}
