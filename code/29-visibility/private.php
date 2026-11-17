<?php

declare(strict_types=1);

final class Book
{
    // Only the class's own methods can read or change $copies.
    public function __construct(public string $title, private int $copies = 1) {}

    public function copies(): int
    {
        return $this->copies;
    }

    public function lend(): void
    {
        if ($this->copies === 0) {
            throw new RuntimeException("All copies of \"$this->title\" are on loan");
        }
        $this->copies--;
    }
}

$book = new Book('Kindred', 2);

try {
    for ($i = 1; $i <= 3; $i++) {
        $book->lend();
        echo "Lent, {$book->copies()} left\n";
    }
} catch (RuntimeException $e) {
    echo $e->getMessage(), "\n";
}
