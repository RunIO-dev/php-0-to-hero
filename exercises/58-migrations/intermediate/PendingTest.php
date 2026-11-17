<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'pending.php');

final class PendingTest extends TestCase
{
    public function testTheNewOnesInOrderOfNumber(): void
    {
        self::assertSame(
            ['003-loan-indexes.sql', '010-member-details.sql'],
            pending(['010-member-details.sql', '001-books.sql', '003-loan-indexes.sql', '002-members.sql'], ['001-books.sql', '002-members.sql']),
        );
    }

    public function testNothingWhenAllHaveRun(): void
    {
        self::assertSame([], pending(['001-books.sql'], ['001-books.sql']));
    }

    /** @return iterable<string, array{list<string>, list<string>}> */
    public static function broken(): iterable
    {
        yield 'no number' => [['books.sql'], []];
        yield 'a short number' => [['1-books.sql'], []];
        yield 'capitals' => [['001-Books.sql'], []];
        yield 'not .sql' => [['001-books.txt'], []];
        yield 'one number twice' => [['001-books.sql', '002-members.sql', '002-loans.sql'], []];
        yield 'a migration that ran has no file' => [['002-members.sql'], ['001-books.sql']];
    }

    /**
     * @param list<string> $files
     * @param list<string> $ran
     */
    #[DataProvider('broken')]
    public function testABrokenHistoryStops(array $files, array $ran): void
    {
        $this->expectException(RuntimeException::class);

        pending($files, $ran);
    }
}
