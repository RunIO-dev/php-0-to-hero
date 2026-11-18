<?php

declare(strict_types=1);

namespace Shelf\Command;

use Doctrine\ORM\EntityManagerInterface;
use Shelf\Book;
use Shelf\BookRepository;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

use function Shelf\readList;

/**
 * php bin/console shelf:catalogue catalogue.json (chapter 81): adds the books of a file that the shelf
 * doesn't have yet, in one transaction. Unlike shelf:import, it may run again and again: a title that
 * is already there is left as it is, copies and all. That makes it safe after every deployment.
 */
#[AsCommand('shelf:catalogue', 'Adds the books of a JSON file that are not on the shelf yet')]
final readonly class CatalogueCommand
{
    public function __construct(private BookRepository $books, private EntityManagerInterface $entities) {}

    public function __invoke(SymfonyStyle $io, #[Argument('A JSON list of books, as in books.json')] string $file): int
    {
        $books = array_map(Book::fromArray(...), readList($file)); // every book checked before one is added
        $new = [];
        foreach ($books as $book) { // new to the shelf, and to the file: a title twice in it is added once
            $key = mb_strtolower($book->title);
            if (!isset($new[$key]) && $this->books->find($book->title) === null) {
                $new[$key] = $book;
            }
        }
        $new = array_values($new);
        $this->entities->wrapInTransaction(function () use ($new): void {
            array_walk($new, $this->books->add(...));
        });
        $io->writeln(sprintf('New books added: %d. Already on the shelf: %d.', count($new), count($books) - count($new)));

        return Command::SUCCESS;
    }
}
