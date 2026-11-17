<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/late-fee.php';

final class FeeTableTest extends TestCase
{
    #[DataProvider('fees')]
    public function testTheFee(int $daysLate, int $cents): void
    {
        self::assertSame($cents, lateFee($daysLate));
    }

    /** @return array<string, array{int, int}> days late and the fee in cents, by name */
    public static function fees(): array
    {
        return [
            'three days late' => [3, 60],
            'a month late' => [30, 600],
            'on time' => [0, 0],
            'early' => [-2, 0],
        ];
    }
}
