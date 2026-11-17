<?php

declare(strict_types=1);

// An interface names what an object can do, not how it does it.
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

final class CollectingNotifier implements Notifier
{
    /** @var list<string> */
    public private(set) array $sent = [];

    public function send(string $name, string $message): void
    {
        $this->sent[] = "$name: $message";
    }
}

// remind() works with any Notifier, and doesn't know which one it got.
function remind(Notifier $notifier, string $name, string $title): void
{
    $notifier->send($name, "\"$title\" is due back tomorrow.");
}

remind(new ScreenNotifier(), 'Amira', 'Kindred');

$collected = new CollectingNotifier();
remind($collected, 'Jonas', 'Kitchen');
remind($collected, 'Kenji', 'Sula');
echo count($collected->sent), " reminders collected\n";
