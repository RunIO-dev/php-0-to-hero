<?php

declare(strict_types=1);

final class Book
{
    public function __construct(public string $title, public int $copies = 1) {}
}

$book = new Book('Kindred', 2);

// Any code anywhere can set any value: nothing keeps the rule "never below 0".
$book->copies -= 3;

echo "$book->title: $book->copies copies\n";
