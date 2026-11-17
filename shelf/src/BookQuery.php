<?php

declare(strict_types=1);

namespace Shelf;

/**
 * What a list of books is filtered by (chapter 70): part of the author's name, in any case, and
 * whether a copy is on the shelf. The empty query finds every book.
 */
final readonly class BookQuery
{
    public function __construct(public string $author = '', public bool $availableOnly = false) {}

    public function matches(Book $book): bool
    {
        return ($this->author === '' || str_contains(mb_strtolower($book->author), mb_strtolower($this->author)))
            && (!$this->availableOnly || $book->isAvailable());
    }
}
