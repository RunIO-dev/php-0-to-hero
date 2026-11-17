<?php

declare(strict_types=1);

// An UPDATE that matches no row is not an error: rowCount() is the only sign.

$db = new PDO('sqlite::memory:');
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

$update = $db->prepare('UPDATE books SET copies = :copies WHERE title = :title');
foreach (['Sula', 'Sulla'] as $title) {
    $update->execute(['copies' => 4, 'title' => $title]);
    echo $update->rowCount() === 0 ? "No book titled \"$title\": nothing changed\n" : "Saved: $title\n";
}
