<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DiscountsTest extends TestCase
{
    public function testEachRule(): void
    {
        require_once subject(__DIR__, 'discounts.php');

        self::assertInstanceOf(Discount::class, new StudentDiscount());
        self::assertInstanceOf(Discount::class, new Voucher(500));
        self::assertSame(1600, (new StudentDiscount())->apply(2000));
        self::assertSame(799, (new StudentDiscount())->apply(999));
        self::assertSame(1500, (new Voucher(500))->apply(2000));
        self::assertSame(0, (new Voucher(500))->apply(300));
    }

    public function testCheckoutUsesThemInOrder(): void
    {
        require_once subject(__DIR__, 'discounts.php');

        self::assertSame(1100, (new Checkout([new StudentDiscount(), new Voucher(500)]))->total(2000));
        self::assertSame(1200, (new Checkout([new Voucher(500), new StudentDiscount()]))->total(2000));
        self::assertSame(2000, (new Checkout([]))->total(2000));
    }
}
