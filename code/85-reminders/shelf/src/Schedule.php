<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Shelf\Message\ExpireHolds;
use Shelf\Message\SendReminders;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule as SymfonySchedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * Shelf's timetable (chapter 85). Symfony Scheduler turns it into a transport, scheduler_shelf: the
 * worker that consumes it gets each message when its time comes, and handles it like any other.
 *   php bin/console messenger:consume async scheduler_shelf
 * Stateful: the cache remembers the last run, so after a restart a missed run is made up, once.
 */
#[AsSchedule('shelf')]
final readonly class Schedule implements ScheduleProviderInterface
{
    public function __construct(private CacheInterface $cache) {}

    public function getSchedule(): SymfonySchedule
    {
        return new SymfonySchedule()
            ->add(RecurringMessage::every('1 hour', new SendReminders()))
            ->add(RecurringMessage::every('1 day', new ExpireHolds(), from: new DateTimeImmutable('03:00')))
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true);
    }
}
