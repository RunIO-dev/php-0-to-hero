<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TagsTest extends TestCase
{
    /** @return array<string, array{string, list<string>}> */
    public static function typed(): array
    {
        return [
            'commas' => ['history,africa,novel', ['history', 'africa', 'novel']],
            'spaces and semicolons' => [' History ;africa,,  Novel ', ['history', 'africa', 'novel']],
            'twice' => ['novel, Novel, poetry', ['novel', 'poetry']],
            'one' => ['Poetry', ['poetry']],
            'nothing' => ['  , ; ', []],
            'umlauts' => ['Übersetzung, Lyrik', ['übersetzung', 'lyrik']],
        ];
    }

    /** @param list<string> $expected */
    #[DataProvider('typed')]
    public function testTags(string $typed, array $expected): void
    {
        require_once subject(__DIR__, 'tags.php');

        self::assertSame($expected, tags($typed));
    }
}
