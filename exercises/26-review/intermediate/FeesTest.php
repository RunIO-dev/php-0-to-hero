<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FeesTest extends TestCase
{
    public function testTheRule(): void
    {
        require_once subject(__DIR__, 'fees.php');

        self::assertSame(150, lateFee(3));
        self::assertSame(1000, lateFee(30));
        self::assertSame(0, lateFee(0));
        self::assertSame(0, lateFee(-2));
    }

    public function testAllThreeFollowIt(): void
    {
        require_once subject(__DIR__, 'fees.php');

        self::assertSame('Kindred: 1000 cents', receiptLine('Kindred', 30));
        self::assertSame('Dear Amira, your fee is now 1000 cents.', reminder('Amira', 30));
        self::assertSame(1150, totalFees([30, 3, -2]));
    }

    public function testTheNumbersAreWrittenOnce(): void
    {
        $tokens = PhpToken::tokenize((string) file_get_contents(subject(__DIR__, 'fees.php')));
        $numbers = array_map(fn(PhpToken $t): string => $t->text, array_filter($tokens, fn(PhpToken $t): bool => $t->is(T_LNUMBER)));
        $count = array_count_values($numbers);

        self::assertSame(1, $count['50'] ?? 0, 'Write 50 once.');
        self::assertSame(1, $count['1000'] ?? 0, 'Write 1000 once.');
    }
}
