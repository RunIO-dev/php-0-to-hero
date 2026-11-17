<?php

declare(strict_types=1);

// Bug: nothing is recorded, so every migration runs again next time.

/**
 * Runs the .sql files in $dir that haven't run in $db yet, in order of name, each in its own
 * transaction, and records each in the table migrations. Returns the names that ran now.
 * A file that changed after it ran: RuntimeException, and nothing runs.
 *
 * @return list<string>
 */
function migrate(PDO $db, string $dir): array
{
    $db->exec('CREATE TABLE IF NOT EXISTS migrations (name TEXT PRIMARY KEY, hash TEXT NOT NULL)');
    $files = glob("$dir/*.sql") ?: [];
    sort($files);
    $done = [];
    $rows = $db->query('SELECT name, hash FROM migrations');
    foreach ($rows === false ? [] : $rows->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $done[$row['name']] = $row['hash'];
    }
    foreach ($files as $file) {
        $name = basename($file);
        if (isset($done[$name]) && $done[$name] !== hash('sha256', (string) file_get_contents($file))) {
            throw new RuntimeException("$name was changed after it ran");
        }
    }
    $ran = [];
    foreach ($files as $file) {
        $name = basename($file);
        if (isset($done[$name])) {
            continue;
        }
        $db->beginTransaction();
        try {
            $db->exec((string) file_get_contents($file));
            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();

            throw $e;
        }
        $ran[] = $name;
    }

    return $ran;
}
