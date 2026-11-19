<?php

declare(strict_types=1);

// A backup of a database in use (chapter 88). The app is in the middle of a change it hasn't
// committed. SQLite's backup API copies the last committed state, page by page, and the copy passes
// SQLite's own check. Works while the app goes on writing; Shelf's Backups does this every night.

$dir = sys_get_temp_dir() . '/shelf-' . bin2hex(random_bytes(4));
mkdir($dir);
$app = new SQLite3("$dir/shelf.sqlite");
$app->exec('CREATE TABLE books (title TEXT NOT NULL)');
$app->exec("INSERT INTO books VALUES ('Kindred'), ('Kitchen')");

$app->exec('BEGIN');
$app->exec("INSERT INTO books VALUES ('Sula')"); // not committed yet

$from = new SQLite3("$dir/shelf.sqlite", SQLITE3_OPEN_READONLY);
$to = new SQLite3("$dir/backup.sqlite");
var_dump($from->backup($to));
echo 'Books in the backup: ', $to->querySingle('SELECT COUNT(*) FROM books'), "\n";
echo "SQLite's check of the backup: ", $to->querySingle('PRAGMA integrity_check'), "\n";

$app->exec('COMMIT');
echo 'Books in the database, after the commit: ', $app->querySingle('SELECT COUNT(*) FROM books'), "\n";

array_map(unlink(...), glob("$dir/*") ?: []);
rmdir($dir);
