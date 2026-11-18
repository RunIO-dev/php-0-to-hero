<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Pager.php');

final class PagerTest extends TestCase
{
    /** @return iterable<string, array{int, int, int, list<int|null>}> current, pages, around, numbers */
    public static function cases(): iterable
    {
        yield 'one page' => [1, 1, 1, [1]];
        yield 'few pages, all of them' => [2, 5, 1, [1, 2, 3, 4, 5]];
        yield 'the middle' => [10, 20, 1, [1, null, 9, 10, 11, null, 20]];
        yield 'the middle, two around' => [10, 20, 2, [1, null, 8, 9, 10, 11, 12, null, 20]];
        yield 'near the start' => [3, 20, 1, [1, 2, 3, 4, null, 20]];
        yield 'the first' => [1, 20, 1, [1, 2, null, 20]];
        yield 'the last' => [20, 20, 1, [1, null, 19, 20]];
        yield 'no neighbours' => [5, 9, 0, [1, null, 5, null, 9]];
    }

    /** @param list<int|null> $numbers */
    #[DataProvider('cases')]
    public function testThePagerShowsTheEndsAndTheNeighbours(int $current, int $pages, int $around, array $numbers): void
    {
        self::assertSame($numbers, Pager::numbers($current, $pages, $around));
    }

    public function testAPageThatDoesntExistIsAMistake(): void
    {
        foreach ([[0, 3], [4, 3], [1, 0]] as [$current, $pages]) {
            try {
                Pager::numbers($current, $pages);
                self::fail("Page $current of $pages was accepted");
            } catch (InvalidArgumentException) {
                $this->addToAssertionCount(1);
            }
        }
    }
}
