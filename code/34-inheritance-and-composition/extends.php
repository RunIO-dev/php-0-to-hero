<?php

declare(strict_types=1);

// Not final: other classes may build on it.
class Item
{
    public function __construct(public readonly string $title) {}

    public function label(): string
    {
        return $this->title;
    }
}

// A Dvd is an Item: it gets title and label(), and adds a running time.
final class Dvd extends Item
{
    public function __construct(string $title, public readonly int $minutes)
    {
        parent::__construct($title);
    }

    #[Override]
    public function label(): string
    {
        return parent::label() . " (DVD, $this->minutes min)";
    }
}

function shelfLabel(Item $item): string
{
    return '[' . $item->label() . ']';
}

echo shelfLabel(new Item('Kindred')), "\n";
echo shelfLabel(new Dvd('Spirited Away', 125)), "\n";
