<?php

declare(strict_types=1);

// Create, read, update, delete: the four things a program does with stored data.

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

// Create
$create = $db->prepare('INSERT INTO books (title, author, year, copies) VALUES (:title, :author, :year, :copies)');
$create->execute(['title' => 'Americanah', 'author' => 'Chimamanda Ngozi Adichie', 'year' => 2013, 'copies' => 2]);
$id = (int) $db->lastInsertId();
echo "Created book $id\n";

// Read one
$read = $db->prepare('SELECT title, copies FROM books WHERE id = :id');
$read->execute(['id' => $id]);
echo 'Read: ', json_encode($read->fetch()), "\n";

// Update
$update = $db->prepare('UPDATE books SET copies = :copies WHERE id = :id');
$update->execute(['copies' => 5, 'id' => $id]);
echo 'Updated ', $update->rowCount(), " row\n";

// Delete
$delete = $db->prepare('DELETE FROM books WHERE id = :id');
$delete->execute(['id' => $id]);
echo 'Deleted ', $delete->rowCount(), " row\n";

$read->execute(['id' => $id]);
echo 'Read again: ', var_export($read->fetch(), true), "\n";
