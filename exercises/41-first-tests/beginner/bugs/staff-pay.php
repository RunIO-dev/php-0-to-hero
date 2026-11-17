<?php

declare(strict_types=1);

/** Staff pay like everyone else. */
function fine(int $daysLate, bool $isStaff = false): int
{
    return $daysLate <= 0 ? 0 : min($daysLate * 20, 500);
}
