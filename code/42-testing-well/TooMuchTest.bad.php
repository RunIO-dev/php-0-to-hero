<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/stock.php';

final class TooMuchTest extends TestCase
{
    public function testTheConstructorSetsTheCopies(): void
    {
        self::assertSame(2, (new Stock(copies: 2))->copies); // tests PHP, not a rule
    }

    public function testEverything(): void
    {
        $stock = new Stock(copies: 2);
        $stock->lend();
        self::assertSame(1, $stock->copies);
        $stock->lend();
        self::assertSame(0, $stock->copies);
        try {
            $stock->lend();
        } catch (NoCopyLeft) {
            // three rules in one test: when it fails, which one broke?
        }
        self::assertSame(0, $stock->copies);
    }
}
