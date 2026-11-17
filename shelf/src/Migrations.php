<?php

declare(strict_types=1);

namespace Shelf;

use PDO;
use RuntimeException;

/**
 * The changes to the database's tables, as numbered .sql files in migrations/ (chapter 58). Each
 * file runs once, in its own transaction, and is recorded in the migrations table, with a hash of
 * its text: a file that changes after it ran is refused, because databases that ran the old text
 * would differ from new ones. To change a table, add a new file.
 */
final readonly class Migrations
{
    public function __construct(private PDO $db, private string $dir) {}

    /** @return list<string> the names of the files that ran now, in order */
    public function apply(): array
    {
        $this->db->exec('CREATE TABLE IF NOT EXISTS migrations (name TEXT PRIMARY KEY, hash TEXT NOT NULL, applied TEXT NOT NULL) STRICT');
        $done = $this->applied();
        $ran = [];
        foreach ($this->files() as $name => $sql) {
            $hash = hash('sha256', str_replace("\r\n", "\n", $sql)); // the same on Windows and Linux
            if (isset($done[$name])) {
                if ($done[$name] !== $hash) {
                    throw new RuntimeException("migrations/$name was changed after it ran; undo the change, and add a new migration instead");
                }
                continue;
            }
            inTransaction($this->db, function () use ($name, $sql, $hash): void {
                $this->db->exec($sql);
                $record = $this->db->prepare('INSERT INTO migrations (name, hash, applied) VALUES (:name, :hash, :applied)');
                $record->execute(['name' => $name, 'hash' => $hash, 'applied' => gmdate('Y-m-d H:i:s')]);
            });
            $ran[] = $name;
        }

        return $ran;
    }

    /** @return list<string> the files that have run in this database, in order */
    public function ran(): array
    {
        $names = array_keys($this->applied());
        sort($names);

        return $names;
    }

    /** @return array<string, string> name => hash of every migration that ran */
    private function applied(): array
    {
        $rows = $this->db->query('SELECT name, hash FROM migrations');
        $done = [];
        foreach ($rows === false ? [] : $rows->fetchAll() as $row) {
            if (is_array($row) && is_string($row['name'] ?? null) && is_string($row['hash'] ?? null)) {
                $done[$row['name']] = $row['hash'];
            }
        }

        return $done;
    }

    /** @return array<string, string> file name => SQL, in the order of their numbers */
    private function files(): array
    {
        $files = [];
        foreach (glob($this->dir . '/*.sql') ?: [] as $path) {
            $name = basename($path);
            if (preg_match('/^\d{3}-[a-z0-9-]+\.sql$/', $name) !== 1) {
                throw new RuntimeException("migrations/$name: name migrations like 004-what-it-does.sql");
            }
            $files[$name] = (string) file_get_contents($path);
        }
        ksort($files);

        return $files;
    }
}
