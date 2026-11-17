<?php

declare(strict_types=1);

final class Book
{
    public string $title;
    public string $author;
}

$book = new Book();
$book->titel = 'Kindred';

echo $book->title, "\n";
