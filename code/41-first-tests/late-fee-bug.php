<?php

declare(strict_types=1);

/** The same rule, with a bug: a book that comes back early earns money. */
function lateFee(int $daysLate): int
{
    return $daysLate * 20;
}
