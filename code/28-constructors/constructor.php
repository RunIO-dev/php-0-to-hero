<?php

declare(strict_types=1);

final class Book
{
    public string $title;
    public string $author;
    public int $year;

    // Runs once, inside new: every book gets its values at the moment it is made.
    public function __construct(string $title, string $author, int $year)
    {
        $this->title = $title;
        $this->author = $author;
        $this->year = $year;
    }
}

$book = new Book('Kindred', 'Octavia E. Butler', 1979);

echo "$book->title by $book->author ($book->year)\n";
