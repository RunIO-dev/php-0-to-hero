<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;

/** One book lent to one member, until a due date. */
final readonly class Loan
{
    public function __construct(
        public Book $book,
        public Member $member,
        public DateTimeImmutable $due,
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
