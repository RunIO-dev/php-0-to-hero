<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';
require_once subject(__DIR__, 'overdue.php');

final class OverdueTest extends TestCase
{
    public function testTheLongestOverdueFirst(): void
    {
        self::assertSame([
            'Beloved: Kenji, 18 days late',
            'Kindred: Kenji, 5 days late',
            'Kindred: Amira, 1 day late',
        ], overdue(testLibrary(), '2026-03-17'));
    }

    public function testALoanDueTodayIsNotLate(): void
    {
        self::assertSame(['Beloved: Kenji, 13 days late'], overdue(testLibrary(), '2026-03-12'));
    }

    public function testADayOfAnotherShapeIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        overdue(testLibrary(), "2026-01-01' OR '1'='1");
    }
}
