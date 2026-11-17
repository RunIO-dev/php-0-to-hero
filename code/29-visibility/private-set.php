<?php

declare(strict_types=1);

final class Book
{
    // Everyone may read $copies; only the class may change it.
    public function __construct(public string $title, public private(set) int $copies = 1) {}

    public function lend(): void
    {
        if ($this->copies === 0) {
            throw new RuntimeException("All copies of \"$this->title\" are on loan");
        }
        $this->copies--;
    }
}

$book = new Book('Kitchen', 2);
$book->lend();

echo "$book->title: $book->copies left\n";
