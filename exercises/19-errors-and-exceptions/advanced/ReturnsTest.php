<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReturnsTest extends TestCase
{
    public function testHandlesEveryReturn(): void
    {
        self::assertSame(
            "Returned: Kindred\nNot returned: Sula is not on loan\nReturned: Kitchen\nStill on loan: Beloved\n",
            output_of(subject(__DIR__, 'returns.php')),
        );
    }

    public function testOtherReturns(): void
    {
        self::assertSame(
            "Not returned: Sula is not on loan\nNot returned: Ulysses is not on loan\nReturned: Beloved\nStill on loan: Kindred, Kitchen\n",
            output_with(subject(__DIR__, 'returns.php'), ['returns' => "['Sula', 'Ulysses', 'Beloved']"]),
        );
    }

    public function testCatchesOnlyNotOnLoan(): void
    {
        $code = (string) file_get_contents(subject(__DIR__, 'returns.php'));

        self::assertDoesNotMatchRegularExpression('/catch\s*\(\s*\\\\?(Throwable|Exception|RuntimeException)\b/', $code, 'Catch NotOnLoan, not a wider type.');
    }
}
