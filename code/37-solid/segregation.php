<?php

declare(strict_types=1);

// I: small interfaces. Code that only reads asks only for reading.
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

// An archive can be read, never written: it implements only what it can keep.
final readonly class Archive implements BookReader
{
    public function titles(): array
    {
        return ['The Epic of Gilgamesh', 'The Tale of Genji'];
    }
}

function catalog(BookReader $books): string
{
    return implode("\n", $books->titles()) . "\n";
}

echo catalog(new Archive());
