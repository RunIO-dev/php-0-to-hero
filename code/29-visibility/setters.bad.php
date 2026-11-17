<?php

declare(strict_types=1);

// ✗ A getter and a setter for every property: private in name only.
final class Book
{
    public function __construct(private int $copies = 1) {}

    public function getCopies(): int
    {
        return $this->copies;
    }

    public function setCopies(int $copies): void
    {
        $this->copies = $copies;
    }
}

$book = new Book(2);
$book->setCopies($book->getCopies() - 3);
