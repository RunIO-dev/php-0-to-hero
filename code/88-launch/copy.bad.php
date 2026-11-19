<?php

declare(strict_types=1);

// ✗ A backup made with copy() (chapter 88). In WAL mode, which many servers use, SQLite writes
// new rows into shelf.sqlite-wal first and moves them into shelf.sqlite later. copy() takes the
// file alone: the copy misses what is in the -wal file, and during a write it may get half of one.

$dir = sys_get_temp_dir() . '/shelf-' . bin2hex(random_bytes(4));
mkdir($dir);
$app = new SQLite3("$dir/shelf.sqlite");
$app->exec('PRAGMA journal_mode = WAL');
$app->exec('CREATE TABLE books (title TEXT NOT NULL)');
$app->exec("INSERT INTO books VALUES ('Kindred'), ('Kitchen')");

copy("$dir/shelf.sqlite", "$dir/copy.sqlite");
echo 'Books in the copy: ', new SQLite3("$dir/copy.sqlite")->querySingle('SELECT COUNT(*) FROM books'), "\n";
