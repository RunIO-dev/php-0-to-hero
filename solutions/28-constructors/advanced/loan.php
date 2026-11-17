<?php

declare(strict_types=1);

final class Book
{
    public function __construct(public string $title) {}
}

final class Member
{
    public function __construct(public string $name) {}
}

final class Loan
{
    public DateTimeImmutable $due;

    public function __construct(
        public Book $book,
        public Member $member,
        DateTimeImmutable $lentOn,
        int $days = 14,
    ) {
        $this->due = $lentOn->modify("+$days days");
    }

    public function daysLate(DateTimeImmutable $today): int
    {
        return $today > $this->due ? (int) $this->due->diff($today)->days : 0;
    }
}
