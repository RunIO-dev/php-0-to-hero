<?php

declare(strict_types=1);

// How a due date reads on a page (chapter 80), as of today, which the constructor gets:
// - label(): "due today", "due tomorrow", "due in 3 days", "1 day overdue", "14 days overdue";
// - level(): "danger" once it is overdue, "warn" from two days before (and on the day), "ok" before that.
// Only the dates count, never the time of day: a loan due on 1 December is "due today" at 23:59 on
// 1 December, and "1 day overdue" at 00:01 on 2 December. Summer time changes nothing either.

final readonly class DueLabel
{
    public function __construct(private DateTimeImmutable $today) {}

    public function label(DateTimeImmutable $due): string
    {
        $days = $this->daysLeft($due);

        return match (true) {
            $days === 0 => 'due today',
            $days === 1 => 'due tomorrow',
            $days > 1 => "due in $days days",
            $days === -1 => '1 day overdue',
            default => -$days . ' days overdue',
        };
    }

    public function level(DateTimeImmutable $due): string
    {
        $days = $this->daysLeft($due);

        return $days < 0 ? 'danger' : ($days <= 2 ? 'warn' : 'ok');
    }

    /** Whole days between the two dates, counted in UTC so that summer time can't add or take an hour. */
    private function daysLeft(DateTimeImmutable $due): int
    {
        $utc = new DateTimeZone('UTC');
        $from = new DateTimeImmutable($this->today->format('Y-m-d'), $utc);
        $to = new DateTimeImmutable($due->format('Y-m-d'), $utc);

        return intdiv($to->getTimestamp() - $from->getTimestamp(), 86400);
    }
}
