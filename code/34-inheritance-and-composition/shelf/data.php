<?php

declare(strict_types=1);

// Shelf's data files: books.json through JsonBookRepository, and members.json.

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

/** The books in a JSON file: a list of objects with title, author, year, copies and an optional isbn. */
final readonly class JsonBookRepository implements BookRepository
{
    public function __construct(private string $path) {}

    public function all(): array
    {
        return array_map(fn(mixed $item): Book => Book::fromArray($item), readList($this->path));
    }

    public function save(array $books): void
    {
        $rows = array_map(fn(Book $book): array => $book->toArray(), $books);
        $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        if (file_put_contents($this->path, $json . "\n") === false) {
            throw new RuntimeException("Cannot save the books to $this->path");
        }
    }
}

/**
 * The members in a JSON file: a list of objects with a name and a role.
 *
 * @return list<Member>
 */
function loadMembers(string $path): array
{
    return array_map(fn(mixed $item): Member => Member::fromArray($item), readList($path));
}
