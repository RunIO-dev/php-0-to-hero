<?php

declare(strict_types=1);

// PDO: one way to talk to many databases. The DSN says which database, and where.

$db = new PDO('sqlite::memory:', options: [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

echo 'Connected to ', $db->getAttribute(PDO::ATTR_DRIVER_NAME), "\n";

$result = $db->query('SELECT title, year FROM books ORDER BY year');
foreach ($result ?: [] as $row) {
    echo "{$row['title']} ({$row['year']})\n";
}
$oldest = $db->query('SELECT year FROM books ORDER BY year LIMIT 1');
var_dump($oldest === false ? null : $oldest->fetch());
