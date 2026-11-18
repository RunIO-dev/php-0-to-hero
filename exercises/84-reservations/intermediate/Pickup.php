<?php

declare(strict_types=1);

// How long a copy set aside waits at the desk (chapter 84). The library is closed on Sundays.
//   Pickup::lastDay($readyOn): seven days after the day the copy was set aside; if that is a Sunday,
//   the Monday after. Only the date counts: the time of day is dropped (00:00).
//   Pickup::expired($lastDay, $today): true from the day after the last day, never on the last day.
//   Pickup::daysLeft($lastDay, $today): whole days until the last day, 0 on it, and never below 0.

final class Pickup
{
    public const int DAYS = 7;

    public static function lastDay(DateTimeImmutable $readyOn): DateTimeImmutable
    {
        return $readyOn; // TODO
    }

    public static function expired(DateTimeImmutable $lastDay, DateTimeImmutable $today): bool
    {
        return false; // TODO
    }

    public static function daysLeft(DateTimeImmutable $lastDay, DateTimeImmutable $today): int
    {
        return 0; // TODO
    }
}
