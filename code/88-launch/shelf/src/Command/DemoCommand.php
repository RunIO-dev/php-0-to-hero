<?php

declare(strict_types=1);

namespace Shelf\Command;

use Psr\Clock\ClockInterface;
use Shelf\Demo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * php bin/console shelf:demo (chapter 88): the demo, back as it was, now instead of at night. Only
 * with SHELF_DEMO=1: on a real library, it would delete every book and member.
 */
#[AsCommand('shelf:demo', 'Puts the demo back as it was (SHELF_DEMO=1 only)')]
final readonly class DemoCommand
{
    public function __construct(private Demo $demo, private ClockInterface $clock) {}

    public function __invoke(SymfonyStyle $io): int
    {
        if (!$this->demo->on) {
            $io->error('Shelf is not a demo (SHELF_DEMO=0): a reset would delete every book and member.');

            return Command::FAILURE;
        }
        $now = $this->demo->reset($this->clock->now());
        $io->writeln("The demo is back as it was: {$now['books']} books, {$now['members']} members.");

        return Command::SUCCESS;
    }
}
