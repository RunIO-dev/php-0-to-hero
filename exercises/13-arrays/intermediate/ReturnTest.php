<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ReturnTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function books(): array
    {
        return [
            'the example' => ["['title' => 'Kindred', 'author' => 'Octavia E. Butler', 'copies' => 2]", "Kindred by Octavia E. Butler: 3 copies\n"],
            'all on loan' => ["['title' => 'Sula', 'author' => 'Toni Morrison', 'copies' => 0]", "Sula by Toni Morrison: 1 copies\n"],
        ];
    }

    #[DataProvider('books')]
    public function testAddsTheReturnedCopy(string $book, string $expected): void
    {
        self::assertSame($expected, output_with(subject(__DIR__, 'return.php'), ['book' => $book]));
    }
}
