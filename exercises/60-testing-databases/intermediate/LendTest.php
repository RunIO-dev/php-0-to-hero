<?php

declare(strict_types=1);

// Exercise 60, intermediate: tests for a transaction.
//
// lend() (lend.php) stores a loan and takes a copy, or, when it refuses, changes nothing. Test it
// on a new SQLite database in memory for each test: run LIBRARY_TABLES, then add the books and
// members your tests need, with INSERT. Test what it returns and what is in the tables after it,
// for a loan that works and for each refusal. Look at the tables after a refusal too, and lend
// again after one. Each file in bugs/ is lend.php with one mistake; your tests must catch them all.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/lend.php';

final class LendTest extends TestCase
{
    public function testALoanTakesACopy(): void
    {
        self::markTestIncomplete('Make the database in setUp(), then write this test.');
    }

    // your other tests here
}

// Check your work: composer exercise 60 intermediate
