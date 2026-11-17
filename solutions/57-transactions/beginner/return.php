<?php

declare(strict_types=1);

// Exercise 57, beginner: a book comes back.

function returnBook(PDO $db, int $loanId): void
{
    $db->beginTransaction();
    try {
        $db->prepare('UPDATE books SET copies = copies + 1 WHERE id = (SELECT book_id FROM loans WHERE id = :id)')
            ->execute(['id' => $loanId]);
        $delete = $db->prepare('DELETE FROM loans WHERE id = :id');
        $delete->execute(['id' => $loanId]);
        if ($delete->rowCount() === 0) {
            throw new OutOfBoundsException("There is no loan $loanId");
        }
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();

        throw $e;
    }
}
