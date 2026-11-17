<?php

declare(strict_types=1);

// Exercise 60, beginner: tests for a class that uses a database.
//
// Members (members.php) adds, finds, changes and removes members. Test it against a real SQLite
// database: in setUp(), make a new one in memory (new PDO('sqlite::memory:')), run Members::TABLE
// in it, and make the Members object. So each test starts empty, and no test depends on another.
// Test what each method promises, also for an id that doesn't exist. Each file in bugs/ is
// members.php with one mistake; your tests must catch them all.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/members.php';

final class MembersTest extends TestCase
{
    public function testAnAddedMemberCanBeFound(): void
    {
        self::markTestIncomplete('Make the database in setUp(), then write this test.');
    }

    // your other tests here
}

// Check your work: composer exercise 60 beginner
