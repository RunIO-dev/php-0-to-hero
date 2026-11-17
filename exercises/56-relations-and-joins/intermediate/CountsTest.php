<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';

final class CountsTest extends TestCase
{
    /** @var array<string, string> */
    private array $queries;

    protected function setUp(): void
    {
        $this->queries = require subject(__DIR__, 'counts.php');
    }

    public function testLoansPerMemberIncludingNone(): void
    {
        self::assertSame([
            ['name' => 'Amira', 'loans' => 2],
            ['name' => 'Kenji', 'loans' => 2],
            ['name' => 'Jonas', 'loans' => 1],
            ['name' => 'Ana', 'loans' => 0],
        ], rowsOf(testLibrary(), $this->queries['members']));
    }

    public function testLoansPerAuthor(): void
    {
        self::assertSame([
            ['author' => 'Octavia E. Butler', 'loans' => 2],
            ['author' => 'Toni Morrison', 'loans' => 2],
            ['author' => 'Chinua Achebe', 'loans' => 1],
        ], rowsOf(testLibrary(), $this->queries['authors']));
    }
}
