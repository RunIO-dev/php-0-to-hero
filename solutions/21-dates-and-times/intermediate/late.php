<?php

declare(strict_types=1);

function daysLate(DateTimeImmutable $dueOn, DateTimeImmutable $returnedOn): int
{
    if ($returnedOn <= $dueOn) {
        return 0;
    }

    return (int) $dueOn->diff($returnedOn)->days;
}
