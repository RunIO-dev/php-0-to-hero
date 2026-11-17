<?php

declare(strict_types=1);

namespace Shelf;

/** Where the books are kept. The rules use this interface, and don't know whether it is a file or, later, a database. */
interface BookRepository
{
    /** @return list<Book> */
    public function all(): array;

    /** @param list<Book> $books */
    public function save(array $books): void;
}
