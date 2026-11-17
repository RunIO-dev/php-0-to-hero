<?php

declare(strict_types=1);

// Exercise 34, intermediate: has a log, isn't one.
//
// Lending extends MemoryLog only to reuse its write() method. So every Lending
// is also a log, with all of a log's methods, and it can only ever write to
// memory. Change Lending so that it doesn't extend anything: its constructor
// takes any Log, and lend() writes to that log. lend() returns and writes
// the same line as now.

interface Log
{
    public function write(string $line): void;
}

class MemoryLog implements Log
{
    /** @var list<string> */
    public private(set) array $lines = [];

    public function write(string $line): void
    {
        $this->lines[] = $line;
    }
}

final class Lending extends MemoryLog
{
    public function lend(string $title, string $name): string
    {
        $line = "$title lent to $name";
        $this->write($line);

        return $line;
    }
}

// Check your work: composer exercise 34 intermediate
