<?php

declare(strict_types=1);

// Two returns at the same moment (chapter 83), as two requests on two connections to one database.
// Each reads the number of copies, adds one, and writes it back: the second write undoes the first.
// UPDATE ... SET copies = copies + 1 lets the database count, one statement at a time.

$file = tempnam(sys_get_temp_dir(), 'shelf');
$connect = fn(): PDO => new PDO("sqlite:$file", options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$desk1 = $connect();
$desk2 = $connect();
$desk1->exec("CREATE TABLE books (title TEXT PRIMARY KEY, copies INTEGER NOT NULL); INSERT INTO books VALUES ('Kindred', 0)");
$copies = fn(PDO $db): int => (int) $db->query("SELECT copies FROM books WHERE title = 'Kindred'")->fetchColumn();

// ✗ Read, add one in PHP, write back: both desks read 0 before either writes.
$read1 = $copies($desk1);
$read2 = $copies($desk2);
$desk1->exec('UPDATE books SET copies = ' . ($read1 + 1) . " WHERE title = 'Kindred'");
$desk2->exec('UPDATE books SET copies = ' . ($read2 + 1) . " WHERE title = 'Kindred'");
echo 'read, then write:  ', $copies($desk1), " copy on the shelf, after two returns\n";

// ✓ The database adds one, to whatever is there when the statement runs.
$desk1->exec("UPDATE books SET copies = 0 WHERE title = 'Kindred'");
$desk1->exec("UPDATE books SET copies = copies + 1 WHERE title = 'Kindred'");
$desk2->exec("UPDATE books SET copies = copies + 1 WHERE title = 'Kindred'");
echo 'copies = copies + 1: ', $copies($desk1), " copies on the shelf\n";

unset($desk1, $desk2);
unlink($file);
