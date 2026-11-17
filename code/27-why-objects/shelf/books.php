<?php

declare(strict_types=1);

// Shelf's data: reading and writing books.json.

/**
 * The books in a JSON file: a list of objects with title, author, year, copies and an optional isbn.
 *
 * @return list<Book>
 */
function loadBooks(string $path): array
{
    $json = is_readable($path) ? file_get_contents($path) : false;
    if ($json === false) {
        throw new RuntimeException("Cannot read the books from $path");
    }
    $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($data) || !array_is_list($data)) {
        throw new UnexpectedValueException("$path must hold a list of books");
    }

    return array_map(fn(mixed $item): Book => toBook($item), $data);
}

/** One book from the file, checked: the file is input, like anything from outside. */
function toBook(mixed $item): Book
{
    $isbn = is_array($item) ? $item['isbn'] ?? null : null;
    if (!is_array($item) || !is_string($item['title'] ?? null) || !is_string($item['author'] ?? null)
        || !is_int($item['year'] ?? null) || !is_int($item['copies'] ?? null) || !($isbn === null || is_string($isbn))) {
        throw new UnexpectedValueException('Each book needs a title, an author, a year and copies: ' . json_encode($item));
    }
    $book = new Book();
    $book->title = $item['title'];
    $book->author = $item['author'];
    $book->year = $item['year'];
    $book->copies = $item['copies'];
    $book->isbn = $isbn;

    return $book;
}

/** @param list<Book> $books */
function saveBooks(string $path, array $books): void
{
    $json = json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    if (file_put_contents($path, $json . "\n") === false) {
        throw new RuntimeException("Cannot save the books to $path");
    }
}
