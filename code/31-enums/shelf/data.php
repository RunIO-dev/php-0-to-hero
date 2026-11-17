<?php

declare(strict_types=1);

// Shelf's data: reading and writing books.json, and reading members.json.

/**
 * The items of a JSON file that holds a list, not yet checked.
 *
 * @return list<mixed>
 */
function readList(string $path): array
{
    $json = is_readable($path) ? file_get_contents($path) : false;
    if ($json === false) {
        throw new RuntimeException("Cannot read $path");
    }
    $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
    if (!is_array($data) || !array_is_list($data)) {
        throw new UnexpectedValueException("$path must hold a list");
    }

    return $data;
}

/**
 * The books in a JSON file: a list of objects with title, author, year, copies and an optional isbn.
 *
 * @return list<Book>
 */
function loadBooks(string $path): array
{
    return array_map(fn(mixed $item): Book => toBook($item), readList($path));
}

/** One book from the file, checked: the file is input, like anything from outside. */
function toBook(mixed $item): Book
{
    $isbn = is_array($item) ? $item['isbn'] ?? null : null;
    if (!is_array($item) || !is_string($item['title'] ?? null) || !is_string($item['author'] ?? null)
        || !is_int($item['year'] ?? null) || !is_int($item['copies'] ?? null) || !($isbn === null || is_string($isbn))) {
        throw new UnexpectedValueException('Each book needs a title, an author, a year and copies: ' . json_encode($item));
    }

    return new Book($item['title'], $item['author'], $item['year'], $item['copies'], $isbn === null ? null : new Isbn($isbn));
}

/**
 * The members in a JSON file: a list of objects with a name and a role.
 *
 * @return list<Member>
 */
function loadMembers(string $path): array
{
    return array_map(fn(mixed $item): Member => toMember($item), readList($path));
}

function toMember(mixed $item): Member
{
    $role = is_array($item) && is_string($item['role'] ?? null) ? Role::tryFrom($item['role']) : null;
    if (!is_array($item) || !is_string($item['name'] ?? null) || $role === null) {
        throw new UnexpectedValueException('Each member needs a name and a role, reader or staff: ' . json_encode($item));
    }

    return new Member($item['name'], $role);
}

/**
 * Writes the books in the same format that loadBooks reads.
 *
 * @param list<Book> $books
 */
function saveBooks(string $path, array $books): void
{
    $rows = array_map(fn(Book $book): array => [
        'title' => $book->title,
        'author' => $book->author,
        'year' => $book->year,
        'copies' => $book->copies,
        'isbn' => $book->isbn?->digits,
    ], $books);
    $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    if (file_put_contents($path, $json . "\n") === false) {
        throw new RuntimeException("Cannot save the books to $path");
    }
}
