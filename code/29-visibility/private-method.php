<?php

declare(strict_types=1);

final class Member
{
    public function __construct(public string $name, private int $loans = 0) {}

    public function greeting(): string
    {
        return "Hello, $this->name. " . $this->loansText();
    }

    // A helper for greeting(): not part of what other code may call.
    private function loansText(): string
    {
        return match ($this->loans) {
            0 => 'You have no books on loan.',
            1 => 'You have 1 book on loan.',
            default => "You have $this->loans books on loan.",
        };
    }
}

echo (new Member('Amira'))->greeting(), "\n";
echo (new Member('Jonas', 2))->greeting(), "\n";
