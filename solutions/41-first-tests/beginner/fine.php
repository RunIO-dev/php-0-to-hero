<?php

declare(strict_types=1);

/**
 * The fine for a late book: 20 cents for each day late, at most 500 cents.
 * Staff pay nothing, and a book that comes back on time or early costs nothing.
 */
function fine(int $daysLate, bool $isStaff = false): int
{
    if ($isStaff || $daysLate <= 0) {
        return 0;
    }

    return min($daysLate * 20, 500);
}
