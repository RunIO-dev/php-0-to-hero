<?php

declare(strict_types=1);

class Item
{
    public readonly string $title;

    public function __construct(string $title)
    {
        $this->title = trim($title);
    }
}

final class Dvd extends Item
{
    public function __construct(string $title, public readonly int $minutes) {}
}

$dvd = new Dvd('Spirited Away', 125);
echo $dvd->title, "\n";
