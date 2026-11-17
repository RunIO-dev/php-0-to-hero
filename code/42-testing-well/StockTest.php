<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/stock.php';

final class StockTest extends TestCase
{
    public function testLendingTakesOneCopy(): void
    {
        // Arrange: what the world looks like before
        $stock = new Stock(copies: 2);

        // Act: the one thing this test is about
        $stock->lend();

        // Assert: what must be true after it
        self::assertSame(1, $stock->copies);
    }

    public function testTheLastCopyCanBeLentOnlyOnce(): void
    {
        $stock = new Stock(copies: 1);
        $stock->lend();

        $this->expectException(NoCopyLeft::class);
        $this->expectExceptionMessage('All copies are on loan');

        $stock->lend();
    }
}
