<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'Backoff.php');

final class BackoffTest extends TestCase
{
    public function testEachWaitIsLongerUpToTheMost(): void
    {
        $backoff = new Backoff(delay: 60, multiplier: 5, maxDelay: 3600, maxTries: 10);

        self::assertSame([60, 300, 1500, 3600, 3600], array_map($backoff->delay(...), [1, 2, 3, 4, 5]));
    }

    public function testTheNextTry(): void
    {
        $failed = new DateTimeImmutable('2026-03-14 08:00:00');
        $backoff = new Backoff();

        self::assertSame('08:01:00', $backoff->retryAt($failed, 1)?->format('H:i:s'));
        self::assertSame('08:02:00', $backoff->retryAt($failed, 2)?->format('H:i:s'));
        self::assertNull($backoff->retryAt($failed, 3), 'Three tries failed: it gives up');
    }

    public function testTheWholeSchedule(): void
    {
        self::assertSame([60, 120], new Backoff()->schedule());
        self::assertSame([60000, 300000, 1500000], new Backoff(60000, 5, 10000000, 4)->schedule(), "Shelf's: in milliseconds");
        self::assertSame([], new Backoff(maxTries: 1)->schedule(), 'One try only: no waiting');
    }

    public function testTriesCountFromOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Backoff()->delay(0);
    }
}
