<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../library.php';
require_once subject(__DIR__, 'sorted.php');

final class SortedTest extends TestCase
{
    /** @return iterable<string, array{string, list<string>}> */
    public static function sorts(): iterable
    {
        yield 'title' => ['title', ['At Swim-Two-Birds', 'Beloved', 'Kindred', 'Sula', 'Things Fall Apart']];
        yield 'newest' => ['newest', ['Beloved', 'Kindred', 'Sula', 'Things Fall Apart', 'At Swim-Two-Birds']];
        yield 'oldest' => ['oldest', ['At Swim-Two-Birds', 'Things Fall Apart', 'Sula', 'Kindred', 'Beloved']];
        yield 'available' => ['available', ['Sula', 'Things Fall Apart', 'At Swim-Two-Birds', 'Beloved', 'Kindred']];
    }

    /** @param list<string> $titles */
    #[DataProvider('sorts')]
    public function testEverySort(string $sort, array $titles): void
    {
        self::assertSame($titles, sortedTitles(testLibrary(), $sort));
    }

    /** @return iterable<string, array{string}> */
    public static function others(): iterable
    {
        yield 'a column' => ['year'];
        yield 'an attack' => ['title; DROP TABLE books'];
        yield 'nothing' => [''];
    }

    #[DataProvider('others')]
    public function testOtherSortsAreRefused(string $sort): void
    {
        $this->expectException(InvalidArgumentException::class);

        sortedTitles(testLibrary(), $sort);
    }
}
