<?php

declare(strict_types=1);

// The same data, checked before it is used: as Book::fromArray does in Shelf.

$book = json_decode((string) file_get_contents(__DIR__ . '/book.json'), true, flags: JSON_THROW_ON_ERROR);
if (!is_array($book) || !is_string($book['title'] ?? null) || !is_int($book['year'] ?? null)) {
    throw new UnexpectedValueException('A book needs a title and a year');
}

echo $book['title'], ' is from ', $book['year'], "\n";
