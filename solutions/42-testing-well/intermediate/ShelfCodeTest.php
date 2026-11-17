<?php

declare(strict_types=1);

// Exercise 42, intermediate: one test, many cases. A solution.

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/shelf-code.php';

final class ShelfCodeTest extends TestCase
{
    #[DataProvider('books')]
    public function testTheShelfCode(string $author, int $year, string $code): void
    {
        self::assertSame($code, shelfCode($author, $year));
    }

    /** @return array<string, array{string, int, string}> */
    public static function books(): array
    {
        return [
            'a middle initial' => ['Octavia E. Butler', 1979, 'BUT-1979'],
            'two names' => ['Chinua Achebe', 1958, 'ACH-1958'],
            'another two' => ['Banana Yoshimoto', 1988, 'YOS-1988'],
            'one name' => ['Homer', 800, 'HOM-800'],
            'an accent' => ['Gabriel García Márquez', 1967, 'MÁR-1967'],
        ];
    }
}
