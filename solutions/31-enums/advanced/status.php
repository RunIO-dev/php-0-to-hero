<?php

declare(strict_types=1);

enum LoanStatus
{
    case Returned;
    case OnTime;
    case DueSoon;
    case Overdue;

    public function label(): string
    {
        return match ($this) {
            LoanStatus::Returned => 'returned',
            LoanStatus::OnTime => 'on loan',
            LoanStatus::DueSoon => 'due soon',
            LoanStatus::Overdue => 'overdue',
        };
    }

    public function needsReminder(): bool
    {
        return $this === LoanStatus::DueSoon || $this === LoanStatus::Overdue;
    }
}

function statusOf(DateTimeImmutable $due, DateTimeImmutable $today, bool $returned): LoanStatus
{
    return match (true) {
        $returned => LoanStatus::Returned,
        $today > $due => LoanStatus::Overdue,
        $today >= $due->modify('-3 days') => LoanStatus::DueSoon,
        default => LoanStatus::OnTime,
    };
}
