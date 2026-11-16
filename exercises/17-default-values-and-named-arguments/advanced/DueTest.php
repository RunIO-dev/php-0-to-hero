<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class DueTest extends TestCase
{
    private const LOANS = ['Kindred' => '2026-12-01', 'Kitchen' => '2026-11-28'];

    public function testDueDateOrNull(): void
    {
        require_once subject(__DIR__, 'due.php');

        self::assertSame('2026-12-01', dueDateOf(self::LOANS, 'Kindred'));
        self::assertNull(dueDateOf(self::LOANS, 'Sula'));
        self::assertSame('?string', (string) (new ReflectionFunction('dueDateOf'))->getReturnType());
    }

    public function testLoanLine(): void
    {
        require_once subject(__DIR__, 'due.php');

        self::assertSame('Kitchen: due back on 2026-11-28', loanLine(self::LOANS, 'Kitchen'));
        self::assertSame('Sula: on the shelf', loanLine(self::LOANS, 'Sula'));
        self::assertSame('Sula: on the shelf', loanLine([], 'Sula'));
    }
}
