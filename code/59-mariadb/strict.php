<?php

declare(strict_types=1);

// MariaDB checks types and lengths, and compares text without case for UNIQUE.

require_once __DIR__ . '/db.php';

freshLibrary();
$db = connect('shelf_app');
$add = $db->prepare('INSERT INTO books (title, author, year) VALUES (:title, :author, :year)');

$tries = [
    ['KINDRED', 'Octavia E. Butler', 1979],
    ['Sula', 'Toni Morrison', 'last year'],
    [str_repeat('Long ', 50), 'Someone', 2000],
    ['Sula', 'Toni Morrison', 3000],
];
foreach ($tries as [$title, $author, $year]) {
    try {
        $add->execute(['title' => $title, 'author' => $author, 'year' => $year]);
        echo "Added $title\n";
    } catch (PDOException $e) {
        echo $e->errorInfo[2] ?? $e->getMessage(), "\n";
    }
}
