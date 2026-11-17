<?php

declare(strict_types=1);

// A typo on a line that this run never reaches: PHP finds it only when it does.

final readonly class Book
{
    public function __construct(public string $title, public string $author) {}
}

function describe(Book $book, bool $long): string
{
    if ($long) {
        return "$book->title by {$book->autor}";
    }

    return $book->title;
}

echo describe(new Book('Kindred', 'Octavia E. Butler'), false), "\n";
