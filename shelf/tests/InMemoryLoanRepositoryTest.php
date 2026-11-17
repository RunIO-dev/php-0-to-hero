<?php

declare(strict_types=1);

namespace Shelf\Tests;

use Shelf\Book;

final class InMemoryLoanRepositoryTest extends LoanRepositoryContract
{
    protected function repositories(Book ...$books): array
    {
        $bookRepository = new InMemoryBookRepository(array_values($books));

        return [$bookRepository, new InMemoryLoanRepository($bookRepository)];
    }
}
