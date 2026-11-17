<?php

declare(strict_types=1);

final class Book
{
    public string $title;
    public int $copies;
}

/** @param list<Book> $books */
function lendAll(array $books): void
{
    foreach ($books as $book) {
        if ($book->copies > 0) {
            $book->copies--;
        }
    }
}

function before(Book $book): Book
{
    return clone $book;
}
