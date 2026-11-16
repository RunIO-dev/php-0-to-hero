<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TitlesTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function lists(): array
    {
        return [
            'the example' => ["['Kindred', 'Kitchen', 'Sula']", "4 titles\nFirst: Kindred\nLast: Beloved\n"],
            'one title' => ["['Sula']", "2 titles\nFirst: Sula\nLast: Beloved\n"],
            'an empty list' => ['[]', "1 titles\nFirst: Beloved\nLast: Beloved\n"],
        ];
    }

    #[DataProvider('lists')]
    public function testAddsAndReadsTheList(string $titles, string $expected): void
    {
        self::assertSame($expected, output_with(subject(__DIR__, 'titles.php'), ['titles' => $titles]));
    }
}
