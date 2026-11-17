<?php

declare(strict_types=1);

// The same two statements in a transaction: both are saved, or neither.

require_once __DIR__ . '/state.php';

$db = library();
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

foreach ([2, 99] as $bookId) {
    $db->beginTransaction();
    try {
        $db->exec("UPDATE books SET copies = copies - 1 WHERE title = 'Sula'");
        $lend = $db->prepare("INSERT INTO loans (book_id, member, due) VALUES (:book, 'Amira', '2026-03-16')");
        $lend->execute(['book' => $bookId]);
        $db->commit();
        echo "Lent book $bookId\n";
    } catch (PDOException $e) {
        $db->rollBack();
        echo "Book $bookId: ", $e->errorInfo[2] ?? $e->getMessage(), ", nothing saved\n";
    }
    echo state($db);
}
