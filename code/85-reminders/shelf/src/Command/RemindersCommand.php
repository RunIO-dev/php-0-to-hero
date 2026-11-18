<?php

declare(strict_types=1);

namespace Shelf\Command;

use Shelf\Message\SendReminders;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * php bin/console shelf:reminders (chapter 85): the reminders that are due, now, instead of at the next
 * hour of the schedule. The emails go into the queue, and the worker sends them.
 */
#[AsCommand('shelf:reminders', 'Sends the reminders that are due now')]
final readonly class RemindersCommand
{
    public function __construct(private MessageBusInterface $bus) {}

    public function __invoke(SymfonyStyle $io): int
    {
        $this->bus->dispatch(new SendReminders()); // not routed to a transport: handled here, at once
        $io->writeln('The reminders that were due are in the queue.');

        return Command::SUCCESS;
    }
}
