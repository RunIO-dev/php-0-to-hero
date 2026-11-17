<?php

declare(strict_types=1);

interface Notifier
{
    public function send(string $name, string $message): void;
}

$notifier = new Notifier();
