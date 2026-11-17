<?php

declare(strict_types=1);

final class Book
{
    public string $title;
    public int $copies;
}

// The function gets the same object, not a copy, so its change stays.
function lendOne(Book $book): void
{
    $book->copies--;
}

$book = new Book();
$book->title = 'Kitchen';
$book->copies = 2;

lendOne($book);
echo "$book->title: $book->copies left\n";

$same = $book;
$same->copies = 5;
echo "$book->title: $book->copies left\n";

$other = clone $book;
$other->copies = 0;
echo "$book->title: $book->copies left\n";
