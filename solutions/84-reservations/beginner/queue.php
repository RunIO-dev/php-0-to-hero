<?php

declare(strict_types=1);

// A member's place in a book's queue (chapter 84). $holds are the book's reservations, oldest first,
// each ['member' => 'Amira', 'status' => 'waiting'] (or 'ready', 'collected', 'cancelled', 'expired').
//   placeInQueue($holds, 'Chen'): 1 for the first who waits, 2 for the next, and so on.
//   Only waiting reservations count. null if the member isn't waiting (not there, or no longer).
// Names are compared as typed: 'amira' isn't 'Amira'.

/** @param list<array{member: string, status: string}> $holds */
function placeInQueue(array $holds, string $member): ?int
{
    $place = 0;
    foreach ($holds as $hold) {
        if ($hold['status'] !== 'waiting') {
            continue;
        }
        $place++;
        if ($hold['member'] === $member) {
            return $place;
        }
    }

    return null;
}
