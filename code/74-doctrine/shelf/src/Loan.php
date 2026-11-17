<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * One book lent to one member, until a due date. Chapter 74: an entity. Its book and its member are
 * entities too: the table holds their IDs, and Doctrine brings the objects back.
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
        #[ORM\Column(type: 'date_immutable')]
        public readonly DateTimeImmutable $due,
    ) {}

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
