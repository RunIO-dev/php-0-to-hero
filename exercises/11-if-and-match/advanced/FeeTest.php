<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FeeTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function days(): array
    {
        return [
            'early' => ['-2', 'Returned on time'],
            'on the day' => ['0', 'Returned on time'],
            'one day' => ['1', 'Late fee: 0.25'],
            'four days' => ['4', 'Late fee: 1.00'],
            'ten days' => ['10', 'Late fee: 2.50'],
            'eleven days' => ['11', 'Late fee: 2.50 (the maximum)'],
            'a month' => ['30', 'Late fee: 2.50 (the maximum)'],
        ];
    }

    #[DataProvider('days')]
    public function testAppliesTheRule(string $daysLate, string $expected): void
    {
        self::assertSame("$expected\n", output_with(subject(__DIR__, 'fee.php'), ['daysLate' => $daysLate]));
    }
}
