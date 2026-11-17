<?php

declare(strict_types=1);

// Exercise 77, intermediate: tests for a Doctrine repository.
//
// books.php is right. Write tests for BookFinder against a real database: testDatabase() gives you an
// empty one; persist() and flush() your books, then ask the finder. Test the filters, the order, the
// pages, the count and the refusals. Each file in bugs/ is books.php with one mistake, and your tests
// must fail on every one. Choose books that make each mistake show: two that a wrong order would swap,
// an author with a % in the filter, a book with no copy left.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/books.php';

final class BookFinderTest extends TestCase
{
    public function testAPartOfTheAuthorsNameFindsTheirBooks(): void
    {
        self::markTestIncomplete('Write this test, then the others.');
    }

    // your other tests here
}

// Check your work: composer exercise 77 intermediate
