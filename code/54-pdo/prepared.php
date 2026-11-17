<?php

declare(strict_types=1);

// A prepared statement: the SQL with placeholders, and the values sent apart from it.

$db = new PDO('sqlite::memory:', options: [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

$byAuthor = $db->prepare('SELECT title FROM books WHERE author = :author ORDER BY title');

foreach (['Toni Morrison', "' OR '1'='1"] as $author) {
    $byAuthor->execute(['author' => $author]);
    echo "$author: ", json_encode(array_column($byAuthor->fetchAll(), 'title')), "\n";
}

$add = $db->prepare('INSERT INTO books (title, author, year) VALUES (?, ?, ?)');
$add->execute(["Midnight's Children", 'Salman Rushdie', 1981]);
echo 'Added book ', $db->lastInsertId(), "\n";

$find = $db->prepare('SELECT title, author FROM books WHERE id = ?');
$find->execute([5]);
var_dump($find->fetch());
$find->execute([99]);
var_dump($find->fetch());
