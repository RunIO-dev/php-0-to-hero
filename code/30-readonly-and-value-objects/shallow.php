<?php

declare(strict_types=1);

final class Book
{
    public function __construct(public string $title, public int $copies) {}
}

final readonly class Loan
{
    public function __construct(public Book $book) {}
}

$loan = new Loan(new Book('Kitchen', 1));

// readonly protects the property $book, not the object it holds.
$loan->book->copies = 7;

echo "{$loan->book->title}: {$loan->book->copies} copies\n";
