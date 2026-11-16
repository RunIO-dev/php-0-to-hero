<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PositionTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function wanted(): array
    {
        return [
            'the first' => ["'Kindred'", "Kindred is number 1 in the list\n"],
            'the last' => ["'Kitchen'", "Kitchen is number 3 in the list\n"],
            'missing' => ["'Sula'", "Sula is not in the list\n"],
        ];
    }

    #[DataProvider('wanted')]
    public function testFindsThePosition(string $wanted, string $expected): void
    {
        self::assertSame($expected, output_with(subject(__DIR__, 'position.php'), ['wanted' => $wanted]));
    }
}
