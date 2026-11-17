<?php

declare(strict_types=1);

namespace Reviews;

/** A reader's review of a book. */
final readonly class Review
{
    public function __construct(public string $author, public string $book, public string $text) {}
}
