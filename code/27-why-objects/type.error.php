<?php

declare(strict_types=1);

final class Book
{
    public string $title;
    public int $year;
}

// A class is a type: this function accepts only Book objects.
function age(Book $book, int $thisYear): int
{
    return $thisYear - $book->year;
}

$book = new Book();
$book->title = 'Things Fall Apart';
$book->year = 1958;

echo "$book->title is ", age($book, 2026), " years old\n";

try {
    echo age(['title' => 'Kindred', 'year' => 1979], 2026);
} catch (TypeError $e) {
    echo 'TypeError: ', $e->getMessage(), "\n";
}
