<?php

declare(strict_types=1);

namespace Shelf\Tests;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shelf\Migrations;

final class MigrationsTest extends TestCase
{
    private PDO $db;
    private string $dir;

    protected function setUp(): void
    {
        $this->db = new PDO('sqlite::memory:', options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $this->dir = sys_get_temp_dir() . '/migrations-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
        file_put_contents("$this->dir/001-notes.sql", 'CREATE TABLE notes (id INTEGER PRIMARY KEY, text TEXT NOT NULL);');
        file_put_contents("$this->dir/002-note-dates.sql", "ALTER TABLE notes ADD COLUMN written TEXT NOT NULL DEFAULT '2026-01-01';");
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob("$this->dir/*") ?: []);
        rmdir($this->dir);
    }

    /** @return list<string> */
    private function columns(): array
    {
        $columns = $this->db->query("SELECT name FROM pragma_table_info('notes')");
        $names = [];
        foreach ($columns === false ? [] : $columns->fetchAll() as $row) {
            $names[] = is_array($row) && is_string($row['name'] ?? null) ? $row['name'] : '?';
        }

        return $names;
    }

    public function testEveryMigrationRunsInOrder(): void
    {
        self::assertSame(['001-notes.sql', '002-note-dates.sql'], new Migrations($this->db, $this->dir)->apply());
        self::assertSame(['id', 'text', 'written'], $this->columns());
    }

    public function testAMigrationRunsOnlyOnce(): void
    {
        new Migrations($this->db, $this->dir)->apply();
        file_put_contents("$this->dir/003-note-index.sql", 'CREATE INDEX notes_by_date ON notes (written);');

        self::assertSame(['003-note-index.sql'], new Migrations($this->db, $this->dir)->apply());
        self::assertSame([], new Migrations($this->db, $this->dir)->apply());
    }

    public function testAMigrationThatFailsChangesNothing(): void
    {
        file_put_contents("$this->dir/003-broken.sql", 'CREATE TABLE tags (name TEXT); CREATE TABLE notes (id INTEGER);');

        try {
            new Migrations($this->db, $this->dir)->apply();
            self::fail('The broken migration must stop');
        } catch (PDOException $e) {
            self::assertStringContainsString('table notes already exists', $e->getMessage());
        }
        $tables = $this->db->query("SELECT name FROM sqlite_schema WHERE type = 'table' AND name = 'tags'");
        self::assertSame([], $tables === false ? [] : $tables->fetchAll());
        self::assertSame(['001-notes.sql', '002-note-dates.sql'], new Migrations($this->db, $this->dir)->ran());
    }

    public function testAMigrationThatRanMustNotChange(): void
    {
        new Migrations($this->db, $this->dir)->apply();
        file_put_contents("$this->dir/001-notes.sql", 'CREATE TABLE notes (id INTEGER PRIMARY KEY, text TEXT);');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('migrations/001-notes.sql was changed after it ran');

        new Migrations($this->db, $this->dir)->apply();
    }

    public function testFilesNeedANumberAndAName(): void
    {
        file_put_contents("$this->dir/notes.sql", '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('name migrations like 004-what-it-does.sql');

        new Migrations($this->db, $this->dir)->apply();
    }
}
