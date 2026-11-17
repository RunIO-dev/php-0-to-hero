<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CardTest extends TestCase
{
    public function testChargeAndPay(): void
    {
        require_once subject(__DIR__, 'card.php');

        $card = new Card();
        self::assertSame(0, $card->balance);

        $card->charge(150);
        $card->charge(50);
        $card->pay(120);
        self::assertSame(80, $card->balance);
    }

    public function testNoOneElseChangesTheBalance(): void
    {
        require_once subject(__DIR__, 'card.php');

        $card = new Card();
        $this->expectException(Error::class);
        $card->balance = 0;
    }

    public function testPayingTooMuch(): void
    {
        require_once subject(__DIR__, 'card.php');

        $card = new Card();
        $card->charge(100);
        $this->expectException(InvalidArgumentException::class);
        $card->pay(101);
    }

    public function testNothingOrLess(): void
    {
        require_once subject(__DIR__, 'card.php');

        $card = new Card();
        foreach ([fn() => $card->charge(0), fn() => $card->charge(-5), fn() => $card->pay(0)] as $call) {
            try {
                $call();
                self::fail('An amount of 0 or less was accepted');
            } catch (InvalidArgumentException) {
            }
        }
        self::assertSame(0, $card->balance);
    }
}
