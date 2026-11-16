<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AvailableTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function shelves(): array
    {
        return [
            'the example' => [
                "[['title' => 'Things Fall Apart', 'copies' => 2], ['title' => 'Kindred', 'copies' => 0], ['title' => 'Kitchen', 'copies' => 1]]",
                "- Things Fall Apart (2)\n- Kitchen (1)\nTotal: 3 copies\n",
            ],
            'all on loan' => ["[['title' => 'Sula', 'copies' => 0]]", "Total: 0 copies\n"],
            'no books' => ['[]', "Total: 0 copies\n"],
        ];
    }

    #[DataProvider('shelves')]
    public function testListsWhatIsOnTheShelf(string $books, string $expected): void
    {
        self::assertSame($expected, output_with(subject(__DIR__, 'available.php'), ['books' => $books]));
    }
}
