<?php

declare(strict_types=1);

final class Book
{
    public string $title;
    public int $year;
}

/**
 * @param list<array{title: string, year: int}> $rows
 * @return list<Book>
 */
function toBooks(array $rows): array
{
    return array_map(function (array $row): Book {
        $book = new Book();
        $book->title = $row['title'];
        $book->year = $row['year'];

        return $book;
    }, $rows);
}

/** @param non-empty-list<Book> $books */
function oldest(array $books): Book
{
    $oldest = $books[0];
    foreach ($books as $book) {
        if ($book->year < $oldest->year) {
            $oldest = $book;
        }
    }

    return $oldest;
}
