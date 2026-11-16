<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StatusTest extends TestCase
{
    /** @return array<string, array{string, string}> */
    public static function copies(): array
    {
        return ['none' => ['0', 'On loan'], 'one' => ['1', 'Last copy'], 'two' => ['2', 'Available'], 'many' => ['12', 'Available']];
    }

    #[DataProvider('copies')]
    public function testPrintsTheStatus(string $copies, string $expected): void
    {
        self::assertSame("$expected\n", output_with(subject(__DIR__, 'status.php'), ['copiesOnShelf' => $copies]));
    }
}
