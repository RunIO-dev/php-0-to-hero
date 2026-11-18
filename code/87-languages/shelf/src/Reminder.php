<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Symfony\Component\Translation\TranslatableMessage;

/** The emails that remind a member of something (chapter 85), each with its subject. */
enum Reminder: string
{
    /** A loan is due back in Loan::REMIND_BEFORE days or fewer. */
    case DueSoon = 'due-soon';
    /** A loan is overdue: on the first day, and again every Loan::REMIND_EVERY days. */
    case Overdue = 'overdue';
    /** A copy is set aside for a reservation (chapter 84). */
    case Pickup = 'pickup';

    /** Chapter 87: a message, which the email translates into the member's language. */
    public function subject(string $title, DateTimeImmutable $day): TranslatableMessage
    {
        $values = ['title' => $title, 'day' => $day];

        return match ($this) {
            self::DueSoon => new TranslatableMessage('“{title}” is due back on {day, date, ::EEEEdMMMM}', $values),
            self::Overdue => new TranslatableMessage('“{title}” was due back on {day, date, ::EEEEdMMMM}', $values),
            self::Pickup => new TranslatableMessage('“{title}” is waiting for you at the desk, until {day, date, ::EEEEdMMMM}', $values),
        };
    }
}
