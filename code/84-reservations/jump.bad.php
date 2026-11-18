<?php

declare(strict_types=1);

// ✗ A copy that comes back goes on the shelf, whoever waits for it. The next person who walks in
// borrows it, and the members in the queue wait for ever.

function giveBack(PDO $db, int $loan, int $book): void
{
    $db->prepare('UPDATE loans SET returned_on = date() WHERE id = ?')->execute([$loan]);
    $db->prepare('UPDATE books SET copies = copies + 1 WHERE id = ?')->execute([$book]);
}
