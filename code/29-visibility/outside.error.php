<?php

declare(strict_types=1);

final class Book
{
    public function __construct(public string $title, public private(set) int $copies = 1) {}
}

$book = new Book('Kindred', 2);
$book->copies = 5;
