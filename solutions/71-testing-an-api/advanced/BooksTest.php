<?php

declare(strict_types=1);

// Exercise 71, advanced: testing a list.
//
// books.php is right. Write tests for booksAnswer(): the pages, their order, the filter, the sort,
// the headers, and the requests it must refuse. Each file in bugs/ is books.php with one mistake, and
// your tests must fail on every one. Edges find bugs: the last page, a page past the end, the largest
// per_page, a filter in capitals.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/books.php';

final class BooksTest extends TestCase
{
    public function testThePagesHoldEveryBookOnce(): void
    {
        $titles = [];
        foreach (['1', '2', '3'] as $page) {
            $titles = [...$titles, ...self::titles(booksAnswer(['page' => $page]))];
        }

        self::assertSame(['Beloved', 'Dawn', 'Kindred', 'Kitchen', 'Parable of the Sower', 'Sula', 'Things Fall Apart'], $titles);
        self::assertSame([], self::titles(booksAnswer(['page' => '4'])));
    }

    public function testTheLinksAndTheTotal(): void
    {
        $first = booksAnswer([]);
        $last = booksAnswer(['page' => '3']);

        self::assertSame('7', $first['headers']['X-Total-Count'] ?? null);
        self::assertSame('</api/books?page=1&per_page=3>; rel="first", </api/books?page=2&per_page=3>; rel="next", </api/books?page=3&per_page=3>; rel="last"', $first['headers']['Link'] ?? null);
        self::assertStringNotContainsString('rel="next"', $last['headers']['Link'] ?? '');
    }

    public function testAFilterInAnyCaseAndItsTotal(): void
    {
        $butler = booksAnswer(['author' => 'BUTLER', 'per_page' => '5']);

        self::assertSame(['Dawn', 'Kindred', 'Parable of the Sower'], self::titles($butler));
        self::assertSame('3', $butler['headers']['X-Total-Count'] ?? null);
    }

    public function testSortedByYearThenTitle(): void
    {
        self::assertSame(['Things Fall Apart', 'Sula', 'Kindred', 'Beloved', 'Dawn'], self::titles(booksAnswer(['sort' => 'year', 'per_page' => '5'])));
    }

    public function testWrongRequestsAre400(): void
    {
        foreach ([['page' => '0'], ['page' => 'x'], ['per_page' => '6'], ['per_page' => '0'], ['sort' => 'author'], ['sort' => 'year DESC']] as $query) {
            $answer = booksAnswer($query);

            self::assertSame([400, 'application/problem+json'], [$answer['status'], $answer['headers']['Content-Type'] ?? null], (string) json_encode($query));
        }
        self::assertSame(200, booksAnswer(['per_page' => '5'])['status']);
    }

    /**
     * @param array{status: int, headers: array<string, string>, body: string} $answer
     * @return list<mixed>
     */
    private static function titles(array $answer): array
    {
        $books = json_decode($answer['body'], true);

        return is_array($books) ? array_column($books, 'title') : [];
    }
}

// Check your work: composer exercise 71 advanced
