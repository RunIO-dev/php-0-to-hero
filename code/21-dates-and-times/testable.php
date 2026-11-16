<?php

declare(strict_types=1);

const LOAN_DAYS = 14;

// The function gets "today" as a parameter, instead of asking the clock itself.
function dueDate(DateTimeImmutable $lentOn): DateTimeImmutable
{
    return $lentOn->modify('+' . LOAN_DAYS . ' days');
}

echo dueDate(new DateTimeImmutable('2026-11-17'))->format('Y-m-d'), "\n";
echo dueDate(new DateTimeImmutable('2026-12-25'))->format('Y-m-d'), "\n";
