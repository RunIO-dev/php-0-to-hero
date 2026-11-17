<?php

declare(strict_types=1);

/** A fine can grow above 500 cents. */
function fine(int $daysLate, bool $isStaff = false): int
{
    return $isStaff ? 0 : max(0, $daysLate) * 20;
}
