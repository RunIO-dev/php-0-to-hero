<?php

declare(strict_types=1);

interface Notifier
{
    public function send(string $name, string $message): void;
}

final class ScreenNotifier implements Notifier
{
    public function send(string $name, string $message): void
    {
        echo "To $name: $message\n";
    }
}

// Reminders has a Notifier; it isn't one. Any Notifier will do.
final readonly class Reminders
{
    public function __construct(private Notifier $notifier, private int $daysBefore) {}

    public function remindIfDue(string $name, string $title, DateTimeImmutable $due, DateTimeImmutable $today): void
    {
        if ($today >= $due->modify("-$this->daysBefore days")) {
            $this->notifier->send($name, "\"$title\" is due back on {$due->format('j F')}.");
        }
    }
}

$reminders = new Reminders(new ScreenNotifier(), 3);
$due = new DateTimeImmutable('2026-11-30');
$reminders->remindIfDue('Amira', 'Kindred', $due, new DateTimeImmutable('2026-11-20'));
$reminders->remindIfDue('Amira', 'Kindred', $due, new DateTimeImmutable('2026-11-28'));
