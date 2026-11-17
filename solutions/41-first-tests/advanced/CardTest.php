<?php

declare(strict_types=1);

// Exercise 41, advanced: tests for a class. A solution.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/card.php';

final class CardTest extends TestCase
{
    public function testValidUpToAndIncludingTheLastDay(): void
    {
        $card = new Card('Amira', new DateTimeImmutable('2026-06-30'));

        self::assertTrue($card->isValidOn(new DateTimeImmutable('2026-06-01')));
        self::assertTrue($card->isValidOn(new DateTimeImmutable('2026-06-30')));
        self::assertFalse($card->isValidOn(new DateTimeImmutable('2026-07-01')));
    }

    public function testRenewingAValidCardAddsAYearToItsExpiry(): void
    {
        $card = new Card('Amira', new DateTimeImmutable('2026-06-30'));

        $renewed = $card->renew(new DateTimeImmutable('2026-06-01'));

        self::assertSame('2027-06-30', $renewed->expires->format('Y-m-d'));
        self::assertSame('Amira', $renewed->member);
        self::assertSame('2026-06-30', $card->expires->format('Y-m-d'));
    }

    public function testRenewingAnExpiredCardCountsFromToday(): void
    {
        $card = new Card('Amira', new DateTimeImmutable('2026-06-30'));

        $renewed = $card->renew(new DateTimeImmutable('2026-09-15'));

        self::assertSame('2027-09-15', $renewed->expires->format('Y-m-d'));
    }
}
