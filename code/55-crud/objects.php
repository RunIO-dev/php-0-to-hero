<?php

declare(strict_types=1);

// Rows in, objects out: the rest of the program never sees an array from the database.

final readonly class Book
{
    public function __construct(public string $title, public int $year, public int $copies) {}

    /** A row from the database, checked: data from outside, as a form or a file is. */
    public static function fromRow(mixed $row): self
    {
        if (!is_array($row) || !is_string($row['title'] ?? null) || !is_int($row['year'] ?? null) || !is_int($row['copies'] ?? null)) {
            throw new UnexpectedValueException('Not a book: ' . json_encode($row));
        }

        return new self($row['title'], $row['year'], $row['copies']);
    }
}

function findBook(PDO $db, string $title): ?Book
{
    $find = $db->prepare('SELECT title, year, copies FROM books WHERE title = :title');
    $find->execute(['title' => $title]);
    $row = $find->fetch(PDO::FETCH_ASSOC);

    return $row === false ? null : Book::fromRow($row);
}

$db = new PDO('sqlite::memory:');
$db->exec((string) file_get_contents(__DIR__ . '/library.sql'));

var_dump(findBook($db, 'Sula'));
var_dump(findBook($db, 'Dune'));
