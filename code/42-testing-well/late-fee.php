<?php

declare(strict_types=1);

/** 20 cents for each day a book is late, and nothing when it comes back early. */
function lateFee(int $daysLate): int
{
    return max(0, $daysLate) * 20;
}
