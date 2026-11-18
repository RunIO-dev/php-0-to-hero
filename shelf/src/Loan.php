<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/**
 * One book lent to one member, until a due date. Chapter 74: an entity. Its book and its member are
 * entities too: the table holds their IDs, and Doctrine brings the objects back. Chapter 83: a loan
 * can be extended, and comes back; returned loans stay, as the library's history.
 */
#[ORM\Entity]
#[ORM\Table(name: 'loans')]
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
    ) {}

    /** How often a loan may be extended (chapter 83), each time by the member's loan period. */
    public const int MAX_RENEWALS = 2;

    /** Chapter 83: how often this loan was extended. */
    #[ORM\Column(options: ['default' => 0])]
    public private(set) int $renewals = 0;

    /** Chapter 83: the day the book came back; a returned loan stays, as the library's history. */
    #[ORM\Column(type: 'date_immutable', nullable: true)]
    public private(set) ?DateTimeImmutable $returnedOn = null;

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
            throw new InvalidArgumentException("\"$title\" is back already.");
        }
        if ($this->isOverdue($today)) {
            throw new InvalidArgumentException("\"$title\" is overdue: please bring it back to the library.");
        }
        if ($this->renewals >= self::MAX_RENEWALS) {
            throw new InvalidArgumentException("\"$title\" was extended " . self::MAX_RENEWALS . ' times already, which is the most.');
        }
        $this->due = $this->due->modify('+' . $this->member->role->loanDays() . ' days');
        $this->renewals++;
    }

    /** The book is back (chapter 83). LoanRepository::giveBack() puts the copy on the shelf with it. */
    public function giveBack(DateTimeImmutable $today): void
    {
        if ($this->isReturned()) {
            throw new InvalidArgumentException("\"{$this->book->title}\" is back already.");
        }
        $this->returnedOn = new DateTimeImmutable($today->format('Y-m-d'));
    }

    /** A loan that starts today. The member's role decides how many days it lasts. */
    public static function start(Book $book, Member $member, DateTimeImmutable $today): self
    {
        return new self($book, $member, $today->modify('+' . $member->role->loanDays() . ' days'));
    }

    public function line(): string
    {
        return "Lent: {$this->book->title} to {$this->member->name}, due back on {$this->due->format('l, j F Y')}";
    }
}
