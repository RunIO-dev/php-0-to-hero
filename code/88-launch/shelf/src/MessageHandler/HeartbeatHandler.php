<?php

declare(strict_types=1);

namespace Shelf\MessageHandler;

use Psr\Clock\ClockInterface;
use Shelf\Heartbeat;
use Shelf\Message\Heartbeat as Beat;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/** The worker's sign of life (chapter 88), read by the health check. */
#[AsMessageHandler]
final readonly class HeartbeatHandler
{
    public function __construct(private Heartbeat $heartbeat, private ClockInterface $clock) {}

    public function __invoke(Beat $message): void
    {
        $this->heartbeat->beat($this->clock->now());
    }
}
