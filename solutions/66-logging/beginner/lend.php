<?php

declare(strict_types=1);

// Exercise 66, beginner: log what happens.

use Psr\Log\LoggerInterface;

function lend(LoggerInterface $log, string $title, string $member, int $copiesLeft): bool
{
    $context = ['member' => $member, 'title' => $title];
    if ($copiesLeft < 1) {
        $log->warning('{member} could not borrow {title}: no copy left', $context);

        return false;
    }
    $log->info('{member} borrowed {title}', $context);

    return true;
}
