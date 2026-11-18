<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\Hold;
use Shelf\HoldStatus;
use Shelf\Member;
use Shelf\Role;

/** A reservation's rules (chapter 84), without a database. */
final class HoldTest extends TestCase
{
    private function hold(): Hold
    {
        return new Hold(new Book('Kindred', 'Octavia E. Butler', 1979, 0), new Member('Amira', Role::Reader), new DateTimeImmutable('2026-03-02'));
    }

    public function testAReservationWaitsFirst(): void
    {
        $hold = $this->hold();

        self::assertSame(HoldStatus::Waiting, $hold->status);
        self::assertNull($hold->readyUntil);
        self::assertFalse($hold->hadACopy());
    }

    public function testACopySetAsideWaitsAWeek(): void
    {
        $hold = $this->hold();

        $hold->ready(new DateTimeImmutable('2026-03-02 17:30'));

        self::assertSame('2026-03-09', $hold->readyUntil?->format('Y-m-d'));
        self::assertFalse($hold->hasExpired(new DateTimeImmutable('2026-03-09 23:59')), 'The last day counts');
        self::assertTrue($hold->hasExpired(new DateTimeImmutable('2026-03-10 00:00')));
    }

    public function testOnlyAWaitingReservationGetsACopy(): void
    {
        $hold = $this->hold();
        $hold->ready(new DateTimeImmutable('2026-03-02'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The reservation of "Kindred" isn\'t waiting.');

        $hold->ready(new DateTimeImmutable('2026-03-03'));
    }

    public function testAReservationThatIsOverStaysOver(): void
    {
        $hold = $this->hold();
        $hold->cancel();

        $this->expectExceptionMessage('The reservation of "Kindred" is over already.');

        $hold->collect();
    }

    public function testOnlyAnUncollectedCopyExpires(): void
    {
        $hold = $this->hold();
        $hold->ready(new DateTimeImmutable('2026-03-02'));

        $this->expectExceptionMessage('The reservation of "Kindred" hasn\'t expired.');

        $hold->expire(new DateTimeImmutable('2026-03-09'));
    }

    public function testOnlyACopyThatWaitsNeedsAMail(): void
    {
        $hold = $this->hold();
        self::assertFalse($hold->needsPickupMail(), 'Still in the queue');

        $hold->ready(new DateTimeImmutable('2026-03-02'));
        self::assertTrue($hold->needsPickupMail());

        $hold->notify(new DateTimeImmutable('2026-03-02'));
        self::assertFalse($hold->needsPickupMail());
    }
}
