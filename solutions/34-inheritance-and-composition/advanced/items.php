<?php

declare(strict_types=1);

class Item
{
    public readonly string $title;

    public function __construct(string $title)
    {
        $this->title = trim($title);
    }

    public function loanDays(): int
    {
        return 14;
    }

    public function label(): string
    {
        return "\"$this->title\"";
    }
}

final class Dvd extends Item
{
    public function __construct(string $title, public readonly int $minutes)
    {
        parent::__construct($title);
    }

    #[Override]
    public function loanDays(): int
    {
        return 7;
    }

    #[Override]
    public function label(): string
    {
        return parent::label() . ' (DVD)';
    }
}

/**
 * @param list<Item> $items
 * @return array<string, string>
 */
function dueDates(array $items, DateTimeImmutable $today): array
{
    $dates = [];
    foreach ($items as $item) {
        $dates[$item->title] = $today->modify('+' . $item->loanDays() . ' days')->format('Y-m-d');
    }

    return $dates;
}
