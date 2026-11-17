<?php

declare(strict_types=1);

namespace Shelf\Command;

use Shelf\Book;
use Shelf\Library;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/** php bin/console shelf:books: every book, and whether a copy is on the shelf. From shelf.php list. */
#[AsCommand('shelf:books', 'Lists the books')]
final readonly class BooksCommand
{
    public function __construct(private Library $library) {}

    public function __invoke(SymfonyStyle $io): int
    {
        $books = $this->library->books();
        $io->writeln(array_map(static fn(Book $book): string => $book->line(), $books));
        $io->writeln(count($books) . ' books');

        return Command::SUCCESS;
    }
}
