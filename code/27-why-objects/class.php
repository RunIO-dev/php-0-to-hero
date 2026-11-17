<?php

declare(strict_types=1);

// A class describes what every book has. Each book is an object of that class.
final class Book
{
    public string $title;
    public string $author;
    public int $year;
    public int $copies;
}

$book = new Book();
$book->title = 'Kindred';
$book->author = 'Octavia E. Butler';
$book->year = 1979;
$book->copies = 2;

echo "$book->title by $book->author ($book->year)\n";
var_dump($book);
