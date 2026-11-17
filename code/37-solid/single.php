<?php

declare(strict_types=1);

// S: each class has one reason to change. The text of a reminder changes for
// other reasons than how it is sent, so they are two classes.
final readonly class ReminderText
{
    public function for(string $name, string $title, DateTimeImmutable $due): string
    {
        return "Dear $name, \"$title\" is due back on {$due->format('j F')}.";
    }
}

final class ScreenSender
{
    public function send(string $text): void
    {
        echo $text, "\n";
    }
}

$text = (new ReminderText())->for('Amira', 'Kindred', new DateTimeImmutable('2026-11-30'));
(new ScreenSender())->send($text);
