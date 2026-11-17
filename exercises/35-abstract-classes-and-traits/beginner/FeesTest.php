<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FeesTest extends TestCase
{
    public function testBothFees(): void
    {
        require_once subject(__DIR__, 'fees.php');

        self::assertInstanceOf(LateFee::class, new BookFee());
        self::assertInstanceOf(LateFee::class, new DvdFee());
        self::assertSame(150, (new BookFee())->forDays(3));
        self::assertSame(300, (new DvdFee())->forDays(3));
        self::assertSame(0, (new DvdFee())->forDays(-1));
    }
}
