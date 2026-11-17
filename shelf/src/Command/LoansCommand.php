<?php

declare(strict_types=1);

namespace Shelf\Command;

use Shelf\Library;
use Shelf\Loan;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/** php bin/console shelf:loans Amira: a member's loans, the soonest due first. From shelf.php loans. */
#[AsCommand('shelf:loans', "Lists a member's loans")]
final readonly class LoansCommand
{
    public function __construct(private Library $library) {}

    public function __invoke(SymfonyStyle $io, #[Argument('The member')] string $name): int
    {
        $loans = $this->library->loansOf($name);
        $io->writeln($loans === [] ? "$name has no books on loan" : "$name has:");
        $io->writeln(array_map(static fn(Loan $loan): string => "- {$loan->book->title}, due back on {$loan->due->format('l, j F Y')}", $loans));

        return Command::SUCCESS;
    }
}
