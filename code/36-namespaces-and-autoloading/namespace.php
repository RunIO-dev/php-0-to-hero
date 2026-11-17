<?php

declare(strict_types=1);

// Everything below belongs to the namespace Shelf: its full name starts with Shelf\.

namespace Shelf;

final readonly class Book
{
    public function __construct(public string $title) {}
}

$book = new Book('Kindred');

echo Book::class, "\n";
echo $book::class, "\n";
echo strtoupper($book->title), "\n";
