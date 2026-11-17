<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/late-fee.php';

final class LooseTest extends TestCase
{
    public function testWithAssertEquals(): void
    {
        self::assertEquals('60', lateFee(3)); // the text "60" counts as equal to the number 60
    }

    public function testWithAssertSame(): void
    {
        self::assertSame('60', lateFee(3)); // the type must match too
    }
}
