<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CanBorrowTest extends TestCase
{
    /** @return array<string, array{array<string, string>, string}> */
    public static function people(): array
    {
        return [
            'a member, a copy, nothing overdue' => [['isMember' => 'true', 'copiesOnShelf' => '1', 'hasOverdueBooks' => 'false'], 'bool(true)'],
            'not a member' => [['isMember' => 'false', 'copiesOnShelf' => '1', 'hasOverdueBooks' => 'false'], 'bool(false)'],
            'no copy on the shelf' => [['isMember' => 'true', 'copiesOnShelf' => '0', 'hasOverdueBooks' => 'false'], 'bool(false)'],
            'an overdue book' => [['isMember' => 'true', 'copiesOnShelf' => '3', 'hasOverdueBooks' => 'true'], 'bool(false)'],
        ];
    }

    /** @param array<string, string> $values */
    #[DataProvider('people')]
    public function testDecidesFromTheThreeValues(array $values, string $expected): void
    {
        self::assertSame($expected, trim(output_with(subject(__DIR__, 'can-borrow.php'), $values)));
    }
}
