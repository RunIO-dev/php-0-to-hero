<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class QueriesTest extends TestCase
{
    /** @return list<array<string, mixed>> */
    private function rows(string $name): array
    {
        $queries = require subject(__DIR__, 'queries.php');
        $db = new PDO('sqlite::memory:');
        $db->exec((string) file_get_contents(__DIR__ . '/../library.sql'));
        $result = $db->query($queries[$name]);

        return $result === false ? [] : $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function testTheTitlesByToniMorrison(): void
    {
        self::assertSame([['title' => 'Beloved'], ['title' => 'Sula']], $this->rows('morrison'));
    }

    public function testTheThreeNewestBooks(): void
    {
        self::assertSame([
            ['title' => 'The Remains of the Day', 'year' => 1989],
            ['title' => 'Kitchen', 'year' => 1988],
            ['title' => 'Beloved', 'year' => 1987],
        ], $this->rows('newest'));
    }

    public function testTheBooksOnTheShelf(): void
    {
        self::assertSame(
            ['Kitchen', 'No Longer Human', 'Sula', 'The Remains of the Day', 'Things Fall Apart'],
            array_column($this->rows('available'), 'title'),
        );
    }
}
