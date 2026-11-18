<?php

declare(strict_types=1);

namespace Shelf\MessageHandler;

use Psr\Clock\ClockInterface;
use Psr\Log\LoggerInterface;
use Shelf\Library;
use Shelf\Message\ExpireHolds;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Passes on the copies nobody collected in time (chapters 84 and 85), as bin/console shelf:holds does.
 * A copy that goes to the next in line gets its "ready" email from the next round of reminders.
 */
#[AsMessageHandler]
final readonly class ExpireHoldsHandler
{
    public function __construct(private Library $library, private ClockInterface $clock, private LoggerInterface $logger) {}

    public function __invoke(ExpireHolds $message): void
    {
        foreach ($this->library->expireHolds($this->clock->now()) as [$hold, $next]) {
            $this->logger->info("Expired: \"{$hold->book->title}\" for {$hold->member->name}; "
                . ($next === null ? 'back on the shelf' : "now for {$next->member->name}"));
        }
    }
}
