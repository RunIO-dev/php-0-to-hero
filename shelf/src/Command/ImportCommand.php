<?php

declare(strict_types=1);

namespace Shelf\Command;

use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Shelf\Book;
use Shelf\BookRepository;
use Shelf\MemberRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

use function Shelf\loadMembers;
use function Shelf\readList;

/**
 * php bin/console shelf:import: the books and members of books.json and members.json into a new,
 * empty database, in one transaction (chapters 54 and 57). From shelf.php, chapter 74.
 */
#[AsCommand('shelf:import', 'Imports books.json and members.json into an empty database')]
final readonly class ImportCommand
{
    public function __construct(
        private BookRepository $books,
        private MemberRepository $members,
        private EntityManagerInterface $entities,
        #[Autowire('%kernel.project_dir%')]
        private string $dir,
    ) {}

    public function __invoke(SymfonyStyle $io): int
    {
        $books = array_map(Book::fromArray(...), readList("$this->dir/books.json"));
        $members = loadMembers("$this->dir/members.json");
        if ($this->books->all() !== []) {
            throw new InvalidArgumentException('The database already has books; nothing was imported.');
        }
        $this->entities->wrapInTransaction(function () use ($books, $members): void {
            array_walk($books, $this->books->add(...));
            array_walk($members, $this->members->add(...));
        });
        $io->writeln('Imported ' . count($books) . ' books and ' . count($members) . ' members');

        return Command::SUCCESS;
    }
}
