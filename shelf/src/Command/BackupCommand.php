<?php

declare(strict_types=1);

namespace Shelf\Command;

use Psr\Clock\ClockInterface;
use Shelf\Backups;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * php bin/console shelf:backup (chapter 88): a backup now, as the worker makes one every night.
 * Before a risky change, or to try a restore.
 */
#[AsCommand('shelf:backup', 'Makes a checked backup of the database and the covers')]
final readonly class BackupCommand
{
    public function __construct(private Backups $backups, private ClockInterface $clock) {}

    public function __invoke(SymfonyStyle $io): int
    {
        $folder = $this->backups->make($this->clock->now());
        $io->writeln(sprintf('Backup %s made and checked; the newest %d are kept.', basename($folder), Backups::KEEP));

        return Command::SUCCESS;
    }
}
