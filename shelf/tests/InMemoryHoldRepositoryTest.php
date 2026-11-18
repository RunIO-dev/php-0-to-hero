<?php

declare(strict_types=1);

namespace Shelf\Tests;

/** The contract for the reservations in an array (chapter 84). */
final class InMemoryHoldRepositoryTest extends HoldRepositoryContract
{
    protected function repositories(): array
    {
        $books = new InMemoryBookRepository([$this->sula]);

        return [$books, new InMemoryHoldRepository($books)];
    }
}
