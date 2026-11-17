<?php

declare(strict_types=1);

// Exercise 71, beginner: tests for an API.
//
// book.php is right. Write tests for bookAnswer(): the status, the Content-Type, and the JSON,
// for a book that exists and for one that doesn't. Each file in bugs/ is book.php with one mistake,
// and your tests must fail on every one. Compare the decoded JSON, not the text: a space doesn't matter.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/book.php';

final class BookTest extends TestCase
{
    public function testAKnownBook(): void
    {
        self::markTestIncomplete('Write this test, then the others.');
    }

    // your other tests here
}

// Check your work: composer exercise 71 beginner
