<?php

declare(strict_types=1);

final class Loan
{
    // A class constant: one value for the whole class, with a type and a name.
    public const int DAYS = 14;
    private const int REMINDER_DAYS = 3;

    public function __construct(public readonly DateTimeImmutable $due) {}

    public function reminderDay(): DateTimeImmutable
    {
        return $this->due->modify('-' . self::REMINDER_DAYS . ' days');
    }
}

echo 'A loan lasts ', Loan::DAYS, " days\n";

$loan = new Loan(new DateTimeImmutable('2026-11-30'));
echo 'Reminder on ', $loan->reminderDay()->format('j F'), "\n";
