<?php

declare(strict_types=1);

final class Book
{
    // Without public, $author is only a parameter, not a property.
    public function __construct(public string $title, string $author) {}
}

$book = new Book('Kindred', 'Octavia E. Butler');
echo $book->author, "\n";
