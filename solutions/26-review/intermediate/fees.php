<?php

declare(strict_types=1);

const FEE_PER_DAY = 50;
const MAX_FEE = 1000;

function lateFee(int $daysLate): int
{
    return min(max($daysLate, 0) * FEE_PER_DAY, MAX_FEE);
}

function receiptLine(string $title, int $daysLate): string
{
    return "$title: " . lateFee($daysLate) . ' cents';
}

function reminder(string $member, int $daysLate): string
{
    return "Dear $member, your fee is now " . lateFee($daysLate) . ' cents.';
}

/** @param list<int> $daysLate one number per book */
function totalFees(array $daysLate): int
{
    return array_sum(array_map(fn(int $days): int => lateFee($days), $daysLate));
}
