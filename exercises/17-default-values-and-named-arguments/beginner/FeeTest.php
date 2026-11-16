<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FeeTest extends TestCase
{
    public function testTheDefaultIs50(): void
    {
        require_once subject(__DIR__, 'fee.php');

        self::assertSame(150, lateFee(3));
        self::assertSame(0, lateFee(0));
    }

    public function testAnotherFeeStillWorks(): void
    {
        require_once subject(__DIR__, 'fee.php');

        self::assertSame(60, lateFee(3, 20));
    }
}
