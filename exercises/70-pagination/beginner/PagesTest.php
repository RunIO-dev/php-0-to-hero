<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'pages.php');

final class PagesTest extends TestCase
{
    public function testAPartPageCountsAsAPage(): void
    {
        self::assertSame(3, pageCount(7, 3));
        self::assertSame(2, pageCount(6, 3));
        self::assertSame(1, pageCount(1, 20));
    }

    public function testNothingFoundIsOneEmptyPage(): void
    {
        self::assertSame(1, pageCount(0, 20));
    }

    public function testTheFirstPageSkipsNothing(): void
    {
        self::assertSame(0, offset(1, 20));
        self::assertSame(20, offset(2, 20));
        self::assertSame(6, offset(3, 3));
    }
}
