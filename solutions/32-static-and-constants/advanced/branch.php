<?php

declare(strict_types=1);

final class Branch
{
    /** @var list<string> */
    private array $titles = [];

    public function __construct(public readonly string $name) {}

    public function add(string $title): void
    {
        $this->titles[] = $title;
    }

    /** @return list<string> */
    public function titles(): array
    {
        return $this->titles;
    }
}
