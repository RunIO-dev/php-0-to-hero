<?php

declare(strict_types=1);

// Exercise 37, beginner: one reason to change.
//
// LoanReport builds the lines of a report AND writes them to a file. The
// wording and the file change for different reasons. Split it:
// - LoanReport keeps only lines(): array, the list of lines, and no longer
//   knows about files (no path, no file functions);
// - a new function saveLines(array $lines, string $path): void writes them,
//   one per line, each ending with a line break.

final class LoanReport
{
    /** @param array<string, string> $loans title => member name */
    public function __construct(private array $loans, private string $path) {}

    public function save(): void
    {
        $lines = [];
        foreach ($this->loans as $title => $name) {
            $lines[] = "$title: $name";
        }
        file_put_contents($this->path, implode("\n", $lines) . "\n");
    }
}

// Check your work: composer exercise 37 beginner
