<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\BookRepository;
use Shelf\Hold;
use Shelf\HoldRepository;
use Shelf\HoldStatus;
use Shelf\Member;
use Shelf\Role;

/** What every HoldRepository must do (chapter 84), in memory and in the database. */
abstract class HoldRepositoryContract extends TestCase
{
    protected Member $amira;
    protected Member $jonas;
    protected Book $sula;
    private DateTimeImmutable $today;

    protected function setUp(): void
    {
        $this->amira = new Member('Amira', Role::Reader);
        $this->jonas = new Member('Jonas', Role::Staff);
        $this->sula = new Book('Sula', 'Toni Morrison', 1973, 0); // every copy out
        $this->today = new DateTimeImmutable('2026-03-02');
    }

    /**
     * New, empty repositories with the book Sula, and the members Amira and Jonas.
     *
     * @return array{BookRepository, HoldRepository}
     */
    abstract protected function repositories(): array;

    private function reserve(HoldRepository $holds, Member $member): Hold
    {
        $holds->add($hold = new Hold($this->sula, $member, $this->today));

        return $hold;
    }

    public function testTheQueueIsFirstComeFirstServed(): void
    {
        [$books, $holds] = $this->repositories();
        $amiras = $this->reserve($holds, $this->amira);
        $jonass = $this->reserve($holds, $this->jonas);
        self::assertSame([1, 2], [$holds->position($amiras), $holds->position($jonass)]);

        $ready = $holds->passOn($this->sula, $this->today);

        self::assertSame($amiras->id(), $ready?->id());
        self::assertSame([HoldStatus::Ready, '2026-03-09'], [$ready->status, $ready->readyUntil?->format('Y-m-d')]);
        self::assertSame(1, $holds->position($jonass), 'Jonas is next');
        self::assertSame(0, $books->find('Sula')?->copies, 'The copy is set aside, not on the shelf');
    }

    public function testACopyNobodyWaitsForGoesOnTheShelf(): void
    {
        [$books, $holds] = $this->repositories();

        self::assertNull($holds->passOn($this->sula, $this->today));

        self::assertSame(1, $books->find('Sula')?->copies);
        self::assertFalse($holds->isWaitedFor($this->sula));
    }

    public function testAMembersOpenReservations(): void
    {
        [, $holds] = $this->repositories();
        $hold = $this->reserve($holds, $this->amira);
        self::assertSame([$hold->id()], array_map(fn(Hold $h): int => $h->id(), $holds->ofMember($this->amira)));
        self::assertSame($hold->id(), $holds->openFor($this->sula, $this->amira)?->id());

        $hold->cancel();
        $holds->release($hold, $this->today);

        self::assertSame([], $holds->ofMember($this->amira));
        self::assertNull($holds->openFor($this->sula, $this->amira));
        self::assertSame(HoldStatus::Cancelled, $holds->find($hold->id())?->status, 'It stays, as history');
    }

    public function testACopyNobodyCollectsExpiresAfterAWeek(): void
    {
        [, $holds] = $this->repositories();
        $this->reserve($holds, $this->amira);
        $holds->passOn($this->sula, $this->today);

        self::assertSame([], $holds->expired(new DateTimeImmutable('2026-03-09')));
        self::assertCount(1, $holds->expired(new DateTimeImmutable('2026-03-10')));
        self::assertCount(1, $holds->ready());
    }

    public function testACancelledCopyGoesToTheNext(): void
    {
        [$books, $holds] = $this->repositories();
        $amiras = $this->reserve($holds, $this->amira);
        $jonass = $this->reserve($holds, $this->jonas);
        $holds->passOn($this->sula, $this->today);

        $amiras->cancel();
        $next = $holds->release($amiras, new DateTimeImmutable('2026-03-04'));

        self::assertSame($jonass->id(), $next?->id());
        self::assertSame('2026-03-11', $next->readyUntil?->format('Y-m-d'));
        self::assertSame(0, $books->find('Sula')?->copies);
    }

    public function testAMembersReservationsCanBeForgotten(): void
    {
        [, $holds] = $this->repositories();
        $hold = $this->reserve($holds, $this->amira);
        $id = $hold->id();
        $hold->cancel();
        $holds->release($hold, $this->today);

        $holds->forget($this->amira);

        self::assertNull($holds->find($id));
    }
}
