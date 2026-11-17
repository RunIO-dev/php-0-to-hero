<?php

declare(strict_types=1);

final class Book
{
    public function __construct(
        public string $title,
        public string $author,
        public int $year,
    ) {}
}

$book = new Book('Kindred');
