<?php

declare(strict_types=1);

// Exercise 51, intermediate: given. A session stores strings, as Shelf's does.

interface Session
{
    public function get(string $key): ?string;

    public function set(string $key, string $value): void;

    public function pull(string $key): ?string;
}

final class ArraySession implements Session
{
    /** @var array<string, string> */
    public array $values = [];

    public function get(string $key): ?string
    {
        return $this->values[$key] ?? null;
    }

    public function set(string $key, string $value): void
    {
        $this->values[$key] = $value;
    }

    public function pull(string $key): ?string
    {
        $value = $this->get($key);
        unset($this->values[$key]);

        return $value;
    }
}
