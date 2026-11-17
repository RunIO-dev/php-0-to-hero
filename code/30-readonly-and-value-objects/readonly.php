<?php

declare(strict_types=1);

final class Book
{
    // Set once, in the constructor, and never again.
    public function __construct(
        public readonly string $title,
        public readonly int $year,
    ) {}
}

$book = new Book('Things Fall Apart', 1958);

echo "$book->title ($book->year)\n";
