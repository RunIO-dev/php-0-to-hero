<?php

declare(strict_types=1);

function lateFee(int $daysLate, int $perDay = 50): int
{
    return $daysLate * $perDay;
}
