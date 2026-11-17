<?php

declare(strict_types=1);

// Exercise 41, beginner: your first tests.
//
// fine.php is right. Write the tests that prove it: one test method for each
// rule in its comment. Each file in bugs/ is fine.php with one mistake, and your
// tests must fail on every one of them. The first test is written for you.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/fine.php';

final class FineTest extends TestCase
{
    public function testTwentyCentsForEachDayLate(): void
    {
        self::assertSame(60, fine(3));
    }

    // your tests here
}

// Check your work: composer exercise 41 beginner
