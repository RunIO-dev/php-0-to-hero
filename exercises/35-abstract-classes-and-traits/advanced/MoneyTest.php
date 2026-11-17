<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testMoney(): void
    {
        require_once subject(__DIR__, 'money.php');

        self::assertSame('1.50 EUR', (new Money(150))->format());
        self::assertSame('0.05 EUR', (new Money(5))->format());
        self::assertTrue((new ReflectionClass(Money::class))->isReadOnly(), 'Make Money readonly.');
        $this->expectException(InvalidArgumentException::class);
        new Money(-1);
    }

    public function testTheSameLines(): void
    {
        require_once subject(__DIR__, 'money.php');

        self::assertSame('Paid: 1.50 EUR', (new Receipt(new Money(150)))->line());
        self::assertSame('Please pay 10.00 EUR', (new FeeNotice(new Money(1000)))->line());
    }

    public function testNoTraitLeft(): void
    {
        require_once subject(__DIR__, 'money.php');

        self::assertFalse(trait_exists('FormatsMoney', false), 'Remove the trait.');
        self::assertSame([], class_uses(Receipt::class));
        self::assertSame([], class_uses(FeeNotice::class));
    }
}
