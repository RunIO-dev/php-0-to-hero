<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FeeTest extends TestCase
{
    public function testPlusGivesANewFee(): void
    {
        require_once subject(__DIR__, 'fee.php');

        $a = new Fee(150);
        $b = new Fee(75);
        $sum = $a->plus($b);

        self::assertSame(225, $sum->cents);
        self::assertSame(150, $a->cents);
        self::assertSame(75, $b->cents);
        self::assertSame('2.25 EUR', $sum->format());
        self::assertSame('0.00 EUR', (new Fee(0))->format());
    }

    public function testNoNegativeFee(): void
    {
        require_once subject(__DIR__, 'fee.php');

        $this->expectException(InvalidArgumentException::class);
        new Fee(-1);
    }

    public function testItCantChange(): void
    {
        require_once subject(__DIR__, 'fee.php');

        self::assertTrue((new ReflectionClass(Fee::class))->isReadOnly(), 'Make the class readonly.');
    }
}
