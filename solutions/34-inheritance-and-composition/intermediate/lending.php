<?php

declare(strict_types=1);

interface Log
{
    public function write(string $line): void;
}

final class MemoryLog implements Log
{
    /** @var list<string> */
    public private(set) array $lines = [];

    public function write(string $line): void
    {
        $this->lines[] = $line;
    }
}

final readonly class Lending
{
    public function __construct(private Log $log) {}

    public function lend(string $title, string $name): string
    {
        $line = "$title lent to $name";
        $this->log->write($line);

        return $line;
    }
}
