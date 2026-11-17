<?php

declare(strict_types=1);

// Exercise 37, advanced: promises that every class can keep.
//
// Archive implements BookRepository, but it can't save, so it throws. Code
// that gets "a BookRepository" can't know that. And Catalog asks for a whole
// repository, but only reads. Change the design:
// - split BookRepository into two interfaces: BookReader with titles(), and
//   BookWriter with save();
// - MemoryBooks implements both; Archive implements only BookReader and has no
//   save() any more;
// - Catalog's constructor asks only for a BookReader.
// Remove BookRepository. What every method returns stays the same.

interface BookRepository
{
    /** @return list<string> */
    public function titles(): array;

    /** @param list<string> $titles */
    public function save(array $titles): void;
}

final class MemoryBooks implements BookRepository
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

final readonly class Archive implements BookRepository
{
    public function titles(): array
    {
        return ['The Epic of Gilgamesh', 'The Tale of Genji'];
    }

    public function save(array $titles): void
    {
        throw new LogicException('The archive is read-only');
    }
}

final readonly class Catalog
{
    public function __construct(private BookRepository $books) {}

    public function text(): string
    {
        return implode(', ', $this->books->titles());
    }
}

// Check your work: composer exercise 37 advanced
