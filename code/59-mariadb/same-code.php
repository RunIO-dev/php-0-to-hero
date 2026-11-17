<?php

declare(strict_types=1);

// The statements of chapters 54 and 55, unchanged, on MariaDB.

require_once __DIR__ . '/db.php';

freshLibrary();
$db = connect('shelf_app');

$find = $db->prepare('SELECT title, author, year, copies FROM books WHERE title = :title');
$find->execute(['title' => 'kindred']);
var_dump($find->fetch());

$add = $db->prepare('INSERT INTO books (title, author, year, copies) VALUES (:title, :author, :year, :copies)');
$add->execute(['title' => "Midnight's Children", 'author' => 'Salman Rushdie', 'year' => 1981, 'copies' => 1]);
echo 'Added book ', $db->lastInsertId(), "\n";

$lend = $db->prepare('UPDATE books SET copies = copies - 1 WHERE title = :title AND copies > 0');
foreach (['Kitchen', 'Kitchen', 'Dune'] as $title) {
    $lend->execute(['title' => $title]);
    echo $lend->rowCount() === 1 ? "Lent: $title\n" : "Not lent: $title\n";
}
