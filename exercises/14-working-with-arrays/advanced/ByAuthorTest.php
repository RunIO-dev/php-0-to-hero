<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ByAuthorTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function authors(): array
    {
        return [
            'three books' => ["'Toni Morrison'", "Toni Morrison: Beloved, Song of Solomon, Sula\n"],
            'one book' => ["'Octavia E. Butler'", "Octavia E. Butler: Kindred\n"],
            'no book' => ["'Chinua Achebe'", "Chinua Achebe: none\n"],
        ];
    }

    #[DataProvider('authors')]
    public function testListsTheAuthorsBooks(string $author, string $expected): void
    {
        self::assertSame($expected, output_with(subject(__DIR__, 'by-author.php'), ['author' => $author]));
    }
}
