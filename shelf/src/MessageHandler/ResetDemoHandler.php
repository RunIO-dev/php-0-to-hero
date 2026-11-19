<?php

declare(strict_types=1);

namespace Shelf\MessageHandler;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Shelf\Demo;
use Shelf\Message\ResetDemo;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** The demo, back as it was (chapter 88), as bin/console shelf:demo puts it back. */
#[AsMessageHandler]
final readonly class ResetDemoHandler
{
    public function __construct(private Demo $demo, private ClockInterface $clock, private LoggerInterface $logger) {}

    public function __invoke(ResetDemo $message): void
    {
        $now = $this->demo->reset($this->clock->now());
        $this->logger->info("Demo reset: {$now['books']} books, {$now['members']} members");
    }
}
