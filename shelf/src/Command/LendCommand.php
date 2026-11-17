<?php

declare(strict_types=1);

namespace Shelf\Command;

use DateTimeImmutable;
use Shelf\Library;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/** php bin/console shelf:lend Kindred Amira: lends a book at the desk. From shelf.php lend. */
#[AsCommand('shelf:lend', 'Lends a book to a member')]
final readonly class LendCommand
{
    public function __construct(private Library $library) {}

    public function __invoke(SymfonyStyle $io, #[Argument('The book')] string $title, #[Argument('The member')] string $name): int
    {
        $io->writeln($this->library->lend($title, $name, new DateTimeImmutable('today'))->line());

        return Command::SUCCESS;
    }
}
