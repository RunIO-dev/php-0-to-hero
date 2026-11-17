<?php

declare(strict_types=1);

// Exercise 60, advanced: tests that need files and a database.
//
// migrate() (migrate.php) runs .sql files from a folder. Each test needs its own new database
// (SQLite in memory) and its own folder of migrations: make the folder in setUp(), under
// sys_get_temp_dir() with a random name, write small .sql files into it in each test, and delete
// the folder and its files in tearDown(). Test the order, that a migration runs only once, that
// one that fails leaves nothing behind, and that a changed one is refused. Each file in bugs/ is
// migrate.php with one mistake; your tests must catch them all.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/migrate.php';

final class MigrateTest extends TestCase
{
    public function testEveryMigrationRunsInOrder(): void
    {
        self::markTestIncomplete('Make the database and the folder in setUp(), then write this test.');
    }

    // your other tests here
}

// Check your work: composer exercise 60 advanced
