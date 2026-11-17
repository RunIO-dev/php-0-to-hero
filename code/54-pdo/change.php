<?php

declare(strict_types=1);

// UPDATE and DELETE with values: rowCount() says how many rows matched.

$db = new PDO('sqlite::memory:');
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

$lend = $db->prepare('UPDATE books SET copies = copies - 1 WHERE title = :title AND copies > 0');
foreach (['Sula', 'Kindred', 'Dune'] as $title) {
    $lend->execute(['title' => $title]);
    echo $lend->rowCount() === 1 ? "Lent: $title\n" : "Not lent: $title\n";
}

$count = $db->query('SELECT COUNT(*) FROM books WHERE copies > 0');
echo 'Books on the shelf: ', $count === false ? '?' : $count->fetchColumn(), "\n";
