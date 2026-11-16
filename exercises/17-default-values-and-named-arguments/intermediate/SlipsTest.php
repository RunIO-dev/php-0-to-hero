<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SlipsTest extends TestCase
{
    public function testPrintsBothSlips(): void
    {
        self::assertSame(
            "Amira borrows Kindred for 14 days (can be renewed)\nJonas borrows Kitchen for 7 days (cannot be renewed)\n",
            output_of(subject(__DIR__, 'slips.php')),
        );
    }

    public function testUsesNamedArguments(): void
    {
        $code = (string) file_get_contents(subject(__DIR__, 'slips.php'));

        self::assertMatchesRegularExpression('/renewable:\s*false/', $code, 'Pass false as a named argument: renewable: false');
        self::assertMatchesRegularExpression('/days:\s*7/', $code, 'Pass 7 as a named argument: days: 7');
    }
}
