<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * A member's reservation of a book (chapter 84), while every copy is out. Reservations of one book
 * form a queue, first come, first served: the order of their numbers. When a copy comes back, the
 * first in the queue gets it: it is set aside at the desk, ready, for PICKUP_DAYS days. Like loans,
 * reservations that are over stay, as history.
 */
#[ORM\Entity]
#[ORM\Table(name: 'holds')]
#[ORM\Index(name: 'holds_queue', columns: ['book_id', 'status'])]
final class Hold
{
    /** How long a copy that is set aside waits at the desk. */
    public const int PICKUP_DAYS = 7;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: HoldStatus::class)]
    public private(set) HoldStatus $status = HoldStatus::Waiting;

    /** The last day to collect the copy at the desk; set when the copy is set aside, and kept after. */
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    public private(set) ?DateTimeImmutable $readyUntil = null;

    /** Chapter 85: the day the member was told that a copy waits for them; null until then. */
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    public private(set) ?DateTimeImmutable $notifiedOn = null;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        public readonly Book $book,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        public readonly Member $member,
        // Not readonly: Doctrine's refresh() sets every column again, with a new date object (chapter 77).
        #[ORM\Column(type: 'date_immutable')]
        public private(set) DateTimeImmutable $placedOn,
    ) {}

    /** The row's number: the order in the queue, and what the form to cancel sends. */
    public function id(): int
    {
        return $this->id ?? throw new \LogicException('This reservation isn\'t stored yet');
    }

    /** The last day to collect a copy that is set aside today. Only the date counts. */
    public static function pickupBy(DateTimeImmutable $today): DateTimeImmutable
    {
        return new DateTimeImmutable($today->format('Y-m-d'))->modify('+' . self::PICKUP_DAYS . ' days');
    }

    /** A copy came back and is set aside for this member. */
    public function ready(DateTimeImmutable $today): void
    {
        if ($this->status !== HoldStatus::Waiting) {
            throw new Refused('The reservation of "{title}" isn\'t waiting.', ['title' => $this->book->title]);
        }
        $this->status = HoldStatus::Ready;
        $this->readyUntil = self::pickupBy($today);
    }

    /** The member borrows the book: the reservation has done its job. */
    public function collect(): void
    {
        $this->close(HoldStatus::Collected);
    }

    /** The member doesn't want the book any more. */
    public function cancel(): void
    {
        $this->close(HoldStatus::Cancelled);
    }

    /** Whether the days to collect the copy are over: from the day after readyUntil. */
    public function hasExpired(DateTimeImmutable $today): bool
    {
        return $this->status === HoldStatus::Ready && $this->readyUntil?->format('Y-m-d') < $today->format('Y-m-d');
    }

    /** Nobody came for the copy in time. */
    public function expire(DateTimeImmutable $today): void
    {
        if (!$this->hasExpired($today)) {
            throw new Refused('The reservation of "{title}" hasn\'t expired.', ['title' => $this->book->title]);
        }
        $this->status = HoldStatus::Expired;
    }

    /** Chapter 85: the member was told today that a copy waits for them. */
    public function notify(DateTimeImmutable $today): void
    {
        $this->notifiedOn = new DateTimeImmutable($today->format('Y-m-d'));
    }

    /** Chapter 85: a copy waits for the member, and nobody has told them yet. */
    public function needsPickupMail(): bool
    {
        return $this->status === HoldStatus::Ready && $this->notifiedOn === null;
    }

    /** Whether a copy was set aside for it: one that must go on when the reservation ends unused. */
    public function hadACopy(): bool
    {
        return $this->readyUntil !== null;
    }

    private function close(HoldStatus $as): void
    {
        if (!$this->status->isOpen()) {
            throw new Refused('The reservation of "{title}" is over already.', ['title' => $this->book->title]);
        }
        $this->status = $as;
    }
}
