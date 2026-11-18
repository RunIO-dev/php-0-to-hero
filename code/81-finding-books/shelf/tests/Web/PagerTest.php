<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shelf\Web\Pager;

final class PagerTest extends TestCase
{
    /** @return iterable<string, array{int, int, list<int|null>}> */
    public static function cases(): iterable
    {
        yield 'one page' => [1, 1, [1]];
        yield 'few pages, all shown' => [2, 4, [1, 2, 3, 4]];
        yield 'the middle of many' => [10, 20, [1, null, 9, 10, 11, null, 20]];
        yield 'near the start, no gap of one' => [3, 20, [1, 2, 3, 4, null, 20]];
        yield 'the last page' => [20, 20, [1, null, 19, 20]];
    }

    /** @param list<int|null> $expected */
    #[DataProvider('cases')]
    public function testThePagerShowsTheEndsAndTheNeighbours(int $current, int $pages, array $expected): void
    {
        self::assertSame($expected, Pager::numbers($current, $pages));
    }

    public function testAPageAfterTheLastIsAMistake(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Pager::numbers(3, 2);
    }
}
