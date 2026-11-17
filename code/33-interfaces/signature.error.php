<?php

declare(strict_types=1);

interface Notifier
{
    public function send(string $name, string $message): void;
}

final class ScreenNotifier implements Notifier
{
    public function send(string $message): void
    {
        echo $message, "\n";
    }
}
