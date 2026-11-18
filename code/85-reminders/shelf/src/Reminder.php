<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;

/** The emails that remind a member of something (chapter 85), each with its subject. */
enum Reminder: string
{
    /** A loan is due back in Loan::REMIND_BEFORE days or fewer. */
    case DueSoon = 'due-soon';
    /** A loan is overdue: on the first day, and again every Loan::REMIND_EVERY days. */
    case Overdue = 'overdue';
    /** A copy is set aside for a reservation (chapter 84). */
    case Pickup = 'pickup';

    public function subject(string $title, DateTimeImmutable $day): string
    {
        $date = $day->format('l, j F');

        return match ($this) {
            self::DueSoon => "\"$title\" is due back on $date",
            self::Overdue => "\"$title\" was due back on $date",
            self::Pickup => "\"$title\" is waiting for you at the desk, until $date",
        };
    }
}
