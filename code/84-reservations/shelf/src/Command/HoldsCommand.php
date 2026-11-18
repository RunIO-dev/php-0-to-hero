<?php

declare(strict_types=1);

namespace Shelf\Command;

use Psr\Clock\ClockInterface;
use Shelf\Library;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * php bin/console shelf:holds (chapter 84): the copies that waited at the desk longer than
 * Hold::PICKUP_DAYS go to the next in line, or back on the shelf. Once a day; chapter 85 runs it
 * on a timer.
 */
#[AsCommand('shelf:holds', 'Passes on the copies nobody collected in time')]
final readonly class HoldsCommand
{
    public function __construct(private Library $library, private ClockInterface $clock) {}

    public function __invoke(SymfonyStyle $io): int
    {
        $expired = $this->library->expireHolds($this->clock->now());
        if ($expired === []) {
            $io->writeln('No reservation has expired.');
        }
        foreach ($expired as [$hold, $next]) {
            $io->writeln("- \"{$hold->book->title}\" waited for {$hold->member->name} until {$hold->readyUntil?->format('j F')}: "
                . ($next === null ? 'back on the shelf' : "now set aside for {$next->member->name}"));
        }

        return Command::SUCCESS;
    }
}
