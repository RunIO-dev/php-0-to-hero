<?php

declare(strict_types=1);

// Bug: Reminds of every loan due tomorrow or earlier, also overdue ones.

interface Clock
{
    public function today(): DateTimeImmutable;
}

interface Mailer
{
    public function send(string $to, string $text): void;
}

/** One book lent to one member, until a due date. */
final readonly class Loan
{
    public function __construct(public string $title, public string $member, public DateTimeImmutable $due) {}
}

/** Reminds each member, once, of every loan that is due tomorrow. */
final readonly class Reminders
{
    public function __construct(private Clock $clock, private Mailer $mailer) {}

    /** @param list<Loan> $loans */
    public function send(array $loans): int
    {
        $tomorrow = $this->clock->today()->modify('+1 day')->format('Y-m-d');
        $sent = 0;
        foreach ($loans as $loan) {
            if ($loan->due->format('Y-m-d') <= $tomorrow) {
                $this->mailer->send($loan->member, "Please bring back \"$loan->title\" tomorrow.");
                $sent++;
            }
        }

        return $sent;
    }
}
