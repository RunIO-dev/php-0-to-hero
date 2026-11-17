<?php

declare(strict_types=1);

final class Book
{
    public int $copies = 1;

    public function isAvailable(): bool
    {
        return $copies > 0;
    }
}

$book = new Book();
var_dump($book->isAvailable());
