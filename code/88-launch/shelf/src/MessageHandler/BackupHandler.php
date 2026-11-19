<?php

declare(strict_types=1);

namespace Shelf\MessageHandler;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Shelf\Backups;
use Shelf\Message\Backup;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** The nightly backup (chapter 88), as bin/console shelf:backup makes it. */
#[AsMessageHandler]
final readonly class BackupHandler
{
    public function __construct(private Backups $backups, private ClockInterface $clock, private LoggerInterface $logger) {}

    public function __invoke(Backup $message): void
    {
        $this->logger->info('Backup made: ' . basename($this->backups->make($this->clock->now())));
    }
}
