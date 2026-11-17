<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FeeTest extends TestCase
{
    public function testTheConstants(): void
    {
        require_once subject(__DIR__, 'fee.php');

        self::assertSame(50, LateFee::PER_DAY);
        self::assertSame(1000, LateFee::MAXIMUM);
    }

    public function testTheFee(): void
    {
        require_once subject(__DIR__, 'fee.php');

        self::assertSame(150, LateFee::forDays(3));
        self::assertSame(1000, LateFee::forDays(20));
        self::assertSame(1000, LateFee::forDays(45));
        self::assertSame(0, LateFee::forDays(0));
        self::assertSame(0, LateFee::forDays(-2));
    }

    public function testTheNumbersAreWrittenOnce(): void
    {
        $tokens = PhpToken::tokenize((string) file_get_contents(subject(__DIR__, 'fee.php')));
        $numbers = array_map(fn(PhpToken $t): string => $t->text, array_filter($tokens, fn(PhpToken $t): bool => $t->is(T_LNUMBER)));

        self::assertSame(1, array_count_values($numbers)['50'] ?? 0, 'Write 50 once, in the constant.');
        self::assertSame(1, array_count_values($numbers)['1000'] ?? 0, 'Write 1000 once, in the constant.');
    }
}
