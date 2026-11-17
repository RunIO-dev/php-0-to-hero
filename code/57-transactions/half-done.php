<?php

declare(strict_types=1);

// Two statements that belong together. The second one fails, and the first one stays done.

require_once __DIR__ . '/state.php';

$db = library();
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

try {
    $db->exec("UPDATE books SET copies = copies - 1 WHERE title = 'Sula'");
    $db->exec("INSERT INTO loans (book_id, member, due) VALUES (99, 'Amira', '2026-03-16')"); // there is no book 99
} catch (PDOException $e) {
    echo 'Failed: ', $e->errorInfo[2] ?? $e->getMessage(), "\n";
}
echo state($db);
