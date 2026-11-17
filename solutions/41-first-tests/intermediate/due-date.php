<?php

declare(strict_types=1);

// Exercise 41, intermediate: write the code for tests that already exist. A solution.

function dueDate(DateTimeImmutable $start, int $days): DateTimeImmutable
{
    $due = $start->modify("+$days days");

    return $due->format('l') === 'Sunday' ? $due->modify('+1 day') : $due;
}
