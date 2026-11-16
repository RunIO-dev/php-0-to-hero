<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SortedTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function lists(): array
    {
        return [
            'the example' => ["['kitchen', 'Sula', 'beloved', 'Kindred']", "beloved, Kindred, kitchen, Sula\n"],
            'already sorted' => ["['Arc', 'bell']", "Arc, bell\n"],
            'one title' => ["['Sula']", "Sula\n"],
        ];
    }

    #[DataProvider('lists')]
    public function testSortsLikePeopleDo(string $titles, string $expected): void
    {
        self::assertSame($expected, output_with(subject(__DIR__, 'sorted.php'), ['titles' => $titles]));
    }
}
