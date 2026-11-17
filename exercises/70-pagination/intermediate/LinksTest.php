<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'links.php');

final class LinksTest extends TestCase
{
    public function testAMiddlePageHasAllFour(): void
    {
        self::assertSame(
            '</books.php?page=1&per_page=2>; rel="first", </books.php?page=1&per_page=2>; rel="prev", '
            . '</books.php?page=3&per_page=2>; rel="next", </books.php?page=4&per_page=2>; rel="last"',
            pageLinks('/books.php', [], 2, 4, 2),
        );
    }

    public function testTheFirstAndLastPagesLeaveOutWhatDoesntExist(): void
    {
        self::assertSame('</b?page=1&per_page=5>; rel="first", </b?page=2&per_page=5>; rel="next", </b?page=2&per_page=5>; rel="last"', pageLinks('/b', [], 1, 2, 5));
        self::assertSame('</b?page=1&per_page=5>; rel="first", </b?page=1&per_page=5>; rel="prev", </b?page=2&per_page=5>; rel="last"', pageLinks('/b', [], 2, 2, 5));
        self::assertSame('</b?page=1&per_page=5>; rel="first", </b?page=1&per_page=5>; rel="last"', pageLinks('/b', [], 1, 1, 5));
    }

    public function testFiltersAreKeptAndEncoded(): void
    {
        self::assertSame(
            '</books.php?author=octavia+e.+butler&page=1&per_page=2>; rel="first", </books.php?author=octavia+e.+butler&page=1&per_page=2>; rel="last"',
            pageLinks('/books.php', ['author' => 'octavia e. butler', 'sort' => ''], 1, 1, 2),
        );
    }

    public function testAPagePastTheEndLeadsBack(): void
    {
        self::assertSame('</b?page=1&per_page=5>; rel="first", </b?page=3&per_page=5>; rel="prev", </b?page=3&per_page=5>; rel="last"', pageLinks('/b', [], 9, 3, 5));
    }
}
