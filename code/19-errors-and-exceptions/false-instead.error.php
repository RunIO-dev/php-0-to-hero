<?php

declare(strict_types=1);

/**
 * @param list<array{title: string, year: int}> $books
 * @return array{title: string, year: int}|false
 */
function findBook(array $books, string $title): array|false
{
    foreach ($books as $book) {
        if ($book['title'] === $title) {
            return $book;
        }
    }

    return false;
}

$books = [['title' => 'Kindred', 'year' => 1979]];
$book = findBook($books, 'Sula');
echo "Year: {$book['year']}\n";
