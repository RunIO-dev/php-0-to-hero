<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValidDateTest extends TestCase
{
    /** @return array<string, array{string, bool}> */
    public static function inputs(): array
    {
        return [
            'a date' => ['2026-11-17', true],
            'a leap day' => ['2028-02-29', true],
            'no leap year' => ['2026-02-29', false],
            '30 February' => ['2026-02-30', false],
            'month 13' => ['2026-13-01', false],
            'without zeros' => ['2026-2-3', false],
            'a word' => ['tomorrow', false],
            'German order' => ['17.11.2026', false],
            'with a time' => ['2026-11-17 10:00', false],
            'empty' => ['', false],
        ];
    }

    #[DataProvider('inputs')]
    public function testOnlyRealDates(string $input, bool $expected): void
    {
        require_once subject(__DIR__, 'valid-date.php');

        self::assertSame($expected, isValidDate($input));
    }
}
