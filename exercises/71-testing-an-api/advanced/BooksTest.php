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
        self::markTestIncomplete('Write this test, then the others.');
    }

    // your other tests here
}

// Check your work: composer exercise 71 advanced
