<?php

declare(strict_types=1);

namespace Shelf\Tests;

use Shelf\Book;
use Shelf\BookRepository;

/** The repository the other tests use keeps the same promises as the real one (chapter 60). */
final class InMemoryBookRepositoryTest extends BookRepositoryContract
{
    protected function repository(Book ...$books): BookRepository
    {
        return new InMemoryBookRepository(array_values($books));
    }
}
