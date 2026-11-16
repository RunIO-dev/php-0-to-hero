<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LateFeeTest extends TestCase
{
    public function testPrintsTheFeeWithTwoDecimals(): void
    {
        self::assertSame("Late fee: 1.40\n", output_of(subject(__DIR__, 'late-fee.php')));
    }

    public function testKeepsTheMoneyInCents(): void
    {
        $floats = array_filter(
            PhpToken::tokenize((string) file_get_contents(subject(__DIR__, 'late-fee.php'))),
            static fn(PhpToken $token): bool => $token->is(T_DNUMBER),
        );
        self::assertSame([], array_values($floats), 'Calculate with whole cents, without numbers like 0.35.');
    }
}
