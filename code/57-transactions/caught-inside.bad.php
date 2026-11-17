<?php

declare(strict_types=1);

// ✗ The error is caught inside the transaction, so the program goes on and commits half the work.

require_once __DIR__ . '/state.php';

$db = library();
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

$db->beginTransaction();
try {
    $db->exec("UPDATE books SET copies = copies - 1 WHERE title = 'Sula'");
    $db->exec("INSERT INTO loans (book_id, member, due) VALUES (99, 'Amira', '2026-03-16')");
} catch (PDOException $e) {
    echo "never mind\n";
}
$db->commit();
