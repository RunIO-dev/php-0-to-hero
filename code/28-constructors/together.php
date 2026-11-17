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

// A loan is made of other objects: which book, to whom, until when.
final class Loan
{
    public function __construct(
        public Book $book,
        public Member $member,
        public DateTimeImmutable $due,
    ) {}

    public function line(): string
    {
        return "{$this->book->title} to {$this->member->name}, due back on {$this->due->format('j F Y')}";
    }
}

$loan = new Loan(new Book('Kitchen'), new Member('Kenji'), new DateTimeImmutable('2026-12-01'));

echo $loan->line(), "\n";
echo $loan->member->name, "\n";
