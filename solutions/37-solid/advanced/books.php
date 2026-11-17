<?php

declare(strict_types=1);

interface BookReader
{
    /** @return list<string> */
    public function titles(): array;
}

interface BookWriter
{
    /** @param list<string> $titles */
    public function save(array $titles): void;
}

final class MemoryBooks implements BookReader, BookWriter
{
    /** @param list<string> $titles */
    public function __construct(private array $titles = []) {}

    public function titles(): array
    {
        return $this->titles;
    }

    public function save(array $titles): void
    {
        $this->titles = $titles;
    }
}

final readonly class Archive implements BookReader
{
    public function titles(): array
    {
        return ['The Epic of Gilgamesh', 'The Tale of Genji'];
    }
}

final readonly class Catalog
{
    public function __construct(private BookReader $books) {}

    public function text(): string
    {
        return implode(', ', $this->books->titles());
    }
}
