<?php

declare(strict_types=1);

// Exercise 66, beginner: log what happens.
//
// lend() is called when a member borrows a book. Log it with the PSR-3 logger it gets:
// - a copy is left: info, the message "{member} borrowed {title}", with member and title in the
//   context; return true;
// - no copy is left: warning, "{member} could not borrow {title}: no copy left", with the same
//   context; return false.

use Psr\Log\LoggerInterface;

function lend(LoggerInterface $log, string $title, string $member, int $copiesLeft): bool
{
    return $copiesLeft > 0;
}
// Check your work: composer exercise 66 beginner
