<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'renewal.php');

final class RenewalTest extends TestCase
{
    /** @return iterable<string, array{string, string, int, ?string}> */
    public static function loans(): iterable
    {
        yield 'early' => ['2026-03-16', '2026-03-02', 0, null];
        yield 'on the due date' => ['2026-03-16', '2026-03-16', 1, null];
        yield 'a day late' => ['2026-03-16', '2026-03-17', 0, 'overdue'];
        yield 'across a month' => ['2026-02-28', '2026-03-01', 0, 'overdue'];
        yield 'extended twice' => ['2026-03-16', '2026-03-10', 2, 'too often'];
        yield 'late and extended twice' => ['2026-03-16', '2026-04-01', 2, 'overdue'];
    }

    #[DataProvider('loans')]
    public function testALoanMayBeExtendedByTheRules(string $due, string $today, int $renewals, ?string $problem): void
    {
        self::assertSame($problem, renewalProblem($due, $today, $renewals));
    }
}
