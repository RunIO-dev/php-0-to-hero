<?php

declare(strict_types=1);

final class Book
{
    public function __construct(public readonly string $title) {}

    public static function describe(): string
    {
        return "A book titled $this->title";
    }
}

echo Book::describe(), "\n";
