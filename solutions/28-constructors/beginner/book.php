<?php

declare(strict_types=1);

final class Book
{
    public function __construct(
        public string $title,
        public string $author,
        public int $year,
        public int $copies = 1,
    ) {}

    public function label(): string
    {
        return "$this->title ($this->year)";
    }
}
