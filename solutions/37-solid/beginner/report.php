<?php

declare(strict_types=1);

final readonly class LoanReport
{
    /** @param array<string, string> $loans title => member name */
    public function __construct(private array $loans) {}

    /** @return list<string> */
    public function lines(): array
    {
        $lines = [];
        foreach ($this->loans as $title => $name) {
            $lines[] = "$title: $name";
        }

        return $lines;
    }
}

/** @param list<string> $lines */
function saveLines(array $lines, string $path): void
{
    file_put_contents($path, implode('', array_map(fn(string $line): string => "$line\n", $lines)));
}
