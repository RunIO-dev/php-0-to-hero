<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';

final class QueriesTest extends TestCase
{
    /** @var array<string, string> */
    private array $queries;

    protected function setUp(): void
    {
        $this->queries = require subject(__DIR__, 'queries.php');
    }

    public function testEveryLoanWithItsBookAndMember(): void
    {
        self::assertSame([
            ['title' => 'Beloved', 'name' => 'Kenji', 'due' => '2026-02-27'],
            ['title' => 'Kindred', 'name' => 'Kenji', 'due' => '2026-03-12'],
            ['title' => 'Kindred', 'name' => 'Amira', 'due' => '2026-03-16'],
            ['title' => 'Things Fall Apart', 'name' => 'Amira', 'due' => '2026-03-20'],
            ['title' => 'Sula', 'name' => 'Jonas', 'due' => '2026-03-30'],
        ], rowsOf(testLibrary(), $this->queries['loans']));
    }

    public function testTheBooksNobodyBorrowed(): void
    {
        self::assertSame([['title' => 'Kitchen']], rowsOf(testLibrary(), $this->queries['idle']));
    }
}
