<?php

declare(strict_types=1);

// The same class: each promoted parameter declares a property and sets it.
final class Book
{
    public function __construct(
        public string $title,
        public string $author,
        public int $year,
        public int $copies = 1,
    ) {}
}

$kindred = new Book('Kindred', 'Octavia E. Butler', 1979, 2);
$kitchen = new Book('Kitchen', 'Banana Yoshimoto', 1988);
$sower = new Book(title: 'Parable of the Sower', author: 'Octavia E. Butler', year: 1993, copies: 3);

foreach ([$kindred, $kitchen, $sower] as $book) {
    echo "$book->title: $book->copies\n";
}
