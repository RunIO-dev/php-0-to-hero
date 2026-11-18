<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * One book lent to one member, until a due date. Chapter 74: an entity. Its book and its member are
 * entities too: the table holds their IDs, and Doctrine brings the objects back. Chapter 83: a loan
 * can be extended, and comes back; returned loans stay, as the library's history. Chapter 86: the
 * day it was lent, which the dashboard counts by.
 */
#[ORM\Entity]
#[ORM\Table(name: 'loans')]
#[ORM\Index(name: 'loans_lent_on', columns: ['lent_on'])] // chapter 86: the dashboard counts loans by this day
final class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        public readonly Book $book,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        public readonly Member $member,
        // Chapter 83: not readonly, so that a loan can be extended.
        #[ORM\Column(type: 'date_immutable')]
        public private(set) DateTimeImmutable $due,
        // Chapter 86: the day it was lent, for the statistics and the history. Not readonly: Doctrine's
        // refresh() sets every column again, with a new date object (chapter 77).
        #[ORM\Column(type: 'date_immutable')]
        public private(set) DateTimeImmutable $lentOn,
    ) {}

    /** How often a loan may be extended (chapter 83), each time by the member's loan period. */
    public const int MAX_RENEWALS = 2;

    /** Chapter 83: how often this loan was extended. */
    #[ORM\Column(options: ['default' => 0])]
    public private(set) int $renewals = 0;

    /** Chapter 83: the day the book came back; a returned loan stays, as the library's history. */
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    public private(set) ?DateTimeImmutable $returnedOn = null;

    /** Chapter 85: a reminder goes this many days before the due date. */
    public const int REMIND_BEFORE = 2;

    /** Chapter 85: an overdue loan is reminded of again after this many days. */
    public const int REMIND_EVERY = 7;

    /** Chapter 85: the day the last reminder about this loan was sent; null if none was. */
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    public private(set) ?DateTimeImmutable $remindedOn = null;

    /** The row's number, for the forms that return or extend this loan. */
    public function id(): int
    {
        return $this->id ?? throw new \LogicException('This loan isn\'t stored yet');
    }

    public function isReturned(): bool
    {
        return $this->returnedOn !== null;
    }

    /** Overdue from the day after the due date: only the dates count, never the time of day. */
    public function isOverdue(DateTimeImmutable $today): bool
    {
        return !$this->isReturned() && $this->due->format('Y-m-d') < $today->format('Y-m-d');
    }

    /** One more loan period (chapter 83): twice at most, and not once it is overdue or back. */
    public function renew(DateTimeImmutable $today): void
    {
        $title = $this->book->title;
        if ($this->isReturned()) {
            throw new Refused('"{title}" is back already.', ['title' => $title]);
        }
        if ($this->isOverdue($today)) {
            throw new Refused('"{title}" is overdue: please bring it back to the library.', ['title' => $title]);
        }
        if ($this->renewals >= self::MAX_RENEWALS) {
            throw new Refused('"{title}" was extended {count} times already, which is the most.', ['title' => $title, 'count' => self::MAX_RENEWALS]);
        }
        $this->due = $this->due->modify('+' . $this->member->role->loanDays() . ' days');
        $this->renewals++;
    }

    /**
     * Which reminder this loan needs today, if any (chapter 85). "Due soon" once, from REMIND_BEFORE days
     * before the due date; "overdue" from the day after it, and again every REMIND_EVERY days. A loan
     * that was extended has a new due date, so it gets its "due soon" again. Only the dates count.
     */
    public function reminderDue(DateTimeImmutable $today): ?Reminder
    {
        if ($this->isReturned()) {
            return null;
        }
        $day = fn(DateTimeImmutable $date): string => $date->format('Y-m-d');
        $last = $this->remindedOn === null ? null : $day($this->remindedOn);
        $soon = $day($this->due->modify('-' . self::REMIND_BEFORE . ' days'));
        if ($this->isOverdue($today)) {
            $again = $day($today->modify('-' . self::REMIND_EVERY . ' days'));

            return $last === null || $last <= $day($this->due) || $last <= $again ? Reminder::Overdue : null;
        }
        if ($day($today) >= $soon) {
            return $last === null || $last < $soon ? Reminder::DueSoon : null;
        }

        return null;
    }

    /** Chapter 85: a reminder about this loan was sent today. LoanRepository::markReminded() stores it. */
    public function remind(DateTimeImmutable $today): void
    {
        $this->remindedOn = new DateTimeImmutable($today->format('Y-m-d'));
    }

    /** The book is back (chapter 83). LoanRepository::giveBack() puts the copy on the shelf with it. */
    public function giveBack(DateTimeImmutable $today): void
    {
        if ($this->isReturned()) {
            throw new Refused('"{title}" is back already.', ['title' => $this->book->title]);
        }
        $this->returnedOn = new DateTimeImmutable($today->format('Y-m-d'));
    }

    /** A loan that starts today. The member's role decides how many days it lasts. */
    public static function start(Book $book, Member $member, DateTimeImmutable $today): self
    {
        $day = new DateTimeImmutable($today->format('Y-m-d'));

        return new self($book, $member, $day->modify('+' . $member->role->loanDays() . ' days'), $day);
    }

    public function line(): string
    {
        return "Lent: {$this->book->title} to {$this->member->name}, due back on {$this->due->format('l, j F Y')}";
    }
}
