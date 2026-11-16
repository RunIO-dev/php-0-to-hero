<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LabelsTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function shelves(): array
    {
        return ['five' => ['5', "Shelf 1\nShelf 2\nShelf 3\nShelf 4\nShelf 5\n"], 'one' => ['1', "Shelf 1\n"], 'none' => ['0', '']];
    }

    #[DataProvider('shelves')]
    public function testPrintsOneLabelPerShelf(string $shelves, string $expected): void
    {
        self::assertSame($expected, output_with(subject(__DIR__, 'labels.php'), ['shelves' => $shelves]));
    }
}
