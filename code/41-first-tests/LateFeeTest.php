<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/late-fee.php';

final class LateFeeTest extends TestCase
{
    public function testTwentyCentsForEachDay(): void
    {
        self::assertSame(60, lateFee(3));
    }

    public function testNothingWhenOnTime(): void
    {
        self::assertSame(0, lateFee(0));
    }

    public function testNothingWhenEarly(): void
    {
        self::assertSame(0, lateFee(-2));
    }
}
