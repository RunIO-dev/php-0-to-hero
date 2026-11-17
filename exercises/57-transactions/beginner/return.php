<?php

declare(strict_types=1);

// Exercise 57, beginner: a book comes back.
//
// returnBook($db, $loanId) deletes the loan and puts one copy of its book back on the shelf
// (copies + 1), in one transaction. If there is no loan with that id, it throws an
// OutOfBoundsException, and nothing has changed.

function returnBook(PDO $db, int $loanId): void
{
    $db->prepare('UPDATE books SET copies = copies + 1 WHERE id = (SELECT book_id FROM loans WHERE id = :id)')
        ->execute(['id' => $loanId]);
    $db->prepare('DELETE FROM loans WHERE id = :id')->execute(['id' => $loanId]);
}
// Check your work: composer exercise 57 beginner
