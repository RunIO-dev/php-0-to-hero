<?php

declare(strict_types=1);

enum LoanStatus
{
    case OnTime;
    case DueSoon;
    case Overdue;

    public function label(): string
    {
        return match ($this) {
            LoanStatus::OnTime => 'on loan',
            LoanStatus::DueSoon => 'due soon',
            LoanStatus::Overdue => 'overdue: please return it',
        };
    }
}

function statusOf(DateTimeImmutable $due, DateTimeImmutable $today): LoanStatus
{
    return match (true) {
        $today > $due => LoanStatus::Overdue,
        $today >= $due->modify('-3 days') => LoanStatus::DueSoon,
        default => LoanStatus::OnTime,
    };
}

$due = new DateTimeImmutable('2026-11-30');

foreach (['2026-11-20', '2026-11-28', '2026-12-02'] as $day) {
    echo "$day: ", statusOf($due, new DateTimeImmutable($day))->label(), "\n";
}
