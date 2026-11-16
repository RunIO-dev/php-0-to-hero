<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReadingListTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function lists(): array
    {
        return [
            'the example' => ["['Kindred', '', 'Kitchen', 'Beloved']", "1. Kindred\n2. Kitchen\n3. Beloved\n"],
            'empty first' => ["['', 'Beloved']", "1. Beloved\n"],
            'only empty entries' => ["['', '']", "No books yet.\n"],
            'no entries' => ['[]', "No books yet.\n"],
        ];
    }

    #[DataProvider('lists')]
    public function testNumbersTheTitles(string $titles, string $expected): void
    {
        self::assertSame($expected, output_with(subject(__DIR__, 'reading-list.php'), ['titles' => $titles]));
    }
}
