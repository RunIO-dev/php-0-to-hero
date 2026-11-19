<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Shelf\Message\Backup;
use Shelf\Message\ExpireHolds;
use Shelf\Message\Heartbeat;
use Shelf\Message\ResetDemo;
use Shelf\Message\SendReminders;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
 * Chapter 88: a heartbeat for the health check, a backup every night, and, in a demo, the reset.
 * The times are the server's time zone: UTC in the official PHP images.
 */
#[AsSchedule('shelf')]
final readonly class Schedule implements ScheduleProviderInterface
{
    public function __construct(
        private CacheInterface $cache,
        #[Autowire('%env(bool:SHELF_DEMO)%')]
        private bool $demo,
    ) {}

    public function getSchedule(): SymfonySchedule
    {
        $schedule = new SymfonySchedule()
            ->add(RecurringMessage::every('1 hour', new SendReminders()))
            ->add(RecurringMessage::every('1 day', new ExpireHolds(), from: new DateTimeImmutable('03:00')))
            ->add(RecurringMessage::every('5 minutes', new Heartbeat()))
            // Before the demo's reset: a backup of the day's library, not of the fresh one.
            ->add(RecurringMessage::every('1 day', new Backup(), from: new DateTimeImmutable('02:30')))
            ->stateful($this->cache)
            ->processOnlyLastMissedRun(true);
        if ($this->demo) {
            $schedule->add(RecurringMessage::every('1 day', new ResetDemo(), from: new DateTimeImmutable('03:00')));
        }

        return $schedule;
    }
}
