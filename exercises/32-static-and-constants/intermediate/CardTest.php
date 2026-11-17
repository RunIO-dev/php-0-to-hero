<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CardTest extends TestCase
{
    public function testFromString(): void
    {
        require_once subject(__DIR__, 'card.php');

        self::assertSame('MC-000124', CardNumber::fromString(' MC-000124 ')->number);
    }

    public function testTheNextNumber(): void
    {
        require_once subject(__DIR__, 'card.php');

        self::assertSame('MC-000125', CardNumber::after(CardNumber::fromString('MC-000124'))->number);
        self::assertSame('MC-001000', CardNumber::after(CardNumber::fromString('MC-000999'))->number);
    }

    public function testInvalidNumbers(): void
    {
        require_once subject(__DIR__, 'card.php');

        foreach (['MC-12', 'mc-000124', 'MC-0001245', 'XX-000124', ''] as $input) {
            try {
                CardNumber::fromString($input);
                self::fail("\"$input\" was accepted");
            } catch (InvalidArgumentException) {
            }
        }
        $this->addToAssertionCount(1);
    }

    public function testOnlyTheNamedConstructors(): void
    {
        require_once subject(__DIR__, 'card.php');

        self::assertTrue((new ReflectionMethod(CardNumber::class, '__construct'))->isPrivate(), 'Make the constructor private.');
    }
}
