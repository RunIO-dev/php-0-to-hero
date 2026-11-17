<?php

declare(strict_types=1);

// Exercise 60, advanced: tests that need files and a database.

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/migrate.php';

final class MigrateTest extends TestCase
{
    private PDO $db;
    private string $dir;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:');
        $this->dir = sys_get_temp_dir() . '/migrate-test-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob("$this->dir/*") ?: []);
        rmdir($this->dir);
    }

    private function write(string $name, string $sql): void
    {
        file_put_contents("$this->dir/$name", $sql);
    }

    /** @return list<string> */
    private function tables(): array
    {
        $tables = $this->db->query("SELECT name FROM sqlite_schema WHERE type = 'table' AND name <> 'migrations' ORDER BY name");

        return array_values(array_map(strval(...), $tables === false ? [] : $tables->fetchAll(PDO::FETCH_COLUMN)));
    }

    public function testEveryMigrationRunsInOrder(): void
    {
        $this->write('002-dates.sql', 'ALTER TABLE notes ADD COLUMN written TEXT');
        $this->write('001-notes.sql', 'CREATE TABLE notes (id INTEGER PRIMARY KEY)');

        self::assertSame(['001-notes.sql', '002-dates.sql'], migrate($this->db, $this->dir));
        self::assertSame(['notes'], $this->tables());
    }

    public function testAMigrationRunsOnlyOnce(): void
    {
        $this->write('001-notes.sql', 'CREATE TABLE notes (id INTEGER PRIMARY KEY)');
        migrate($this->db, $this->dir);
        $this->write('002-tags.sql', 'CREATE TABLE tags (name TEXT)');

        self::assertSame(['002-tags.sql'], migrate($this->db, $this->dir));
        self::assertSame([], migrate($this->db, $this->dir));
    }

    public function testAMigrationThatFailsLeavesNothing(): void
    {
        $this->write('001-broken.sql', 'CREATE TABLE tags (name TEXT); CREATE TABLE tags (name TEXT);');

        try {
            migrate($this->db, $this->dir);
            self::fail('A broken migration must stop');
        } catch (PDOException) {
        }
        self::assertSame([], $this->tables());
    }

    public function testAChangedMigrationIsRefused(): void
    {
        $this->write('001-notes.sql', 'CREATE TABLE notes (id INTEGER PRIMARY KEY)');
        migrate($this->db, $this->dir);
        $this->write('001-notes.sql', 'CREATE TABLE notes (id INTEGER PRIMARY KEY, text TEXT)');

        $this->expectException(RuntimeException::class);

        migrate($this->db, $this->dir);
    }
}
