<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FixInputTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function inputs(): array
    {
        return [
            'four days' => ["'4'", "Fee: 200 cents\n"],
            'none' => ["'0'", "Fee: 0 cents\n"],
            'ten days' => ["'10'", "Fee: 500 cents\n"],
        ];
    }

    #[DataProvider('inputs')]
    public function testPrintsTheFee(string $input, string $expected): void
    {
        self::assertSame($expected, output_with(subject(__DIR__, 'fix-input.php'), ['input' => $input]));
    }

    public function testLateFeeKeepsItsType(): void
    {
        $code = (string) file_get_contents(subject(__DIR__, 'fix-input.php'));

        self::assertStringContainsString('function lateFee(int $daysLate): int', $code, 'Leave lateFee() as it is; change the value instead.');
    }
}
