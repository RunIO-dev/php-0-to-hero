<?php

declare(strict_types=1);

/** A book that comes back early still costs money. */
function fine(int $daysLate, bool $isStaff = false): int
{
    return $isStaff ? 0 : min(abs($daysLate) * 20, 500);
}
