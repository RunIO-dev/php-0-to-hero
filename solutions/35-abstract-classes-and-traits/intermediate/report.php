<?php

declare(strict_types=1);

abstract class Report
{
    final public function render(): string
    {
        $rows = $this->rows();
        $count = count($rows) === 1 ? '1 row' : count($rows) . ' rows';

        return "== {$this->title()} ==\n" . implode('', array_map(fn(string $row): string => "$row\n", $rows)) . "($count)\n";
    }

    abstract protected function title(): string;

    /** @return list<string> */
    abstract protected function rows(): array;
}

final class TitlesReport extends Report
{
    /** @param list<string> $titles */
    public function __construct(private readonly array $titles) {}

    protected function title(): string
    {
        return 'Titles';
    }

    protected function rows(): array
    {
        return $this->titles;
    }
}

final class LoansReport extends Report
{
    /** @param array<string, string> $loans */
    public function __construct(private readonly array $loans) {}

    protected function title(): string
    {
        return 'Loans';
    }

    protected function rows(): array
    {
        $rows = [];
        foreach ($this->loans as $title => $name) {
            $rows[] = "$title: $name";
        }

        return $rows;
    }
}
