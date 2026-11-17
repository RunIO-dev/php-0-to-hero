<?php

declare(strict_types=1);

// Exercise 28, advanced: a loan and its due date.
//
// Book and Member are given. Write a class Loan:
// - its constructor takes a Book, a Member, the day it was lent on
//   (DateTimeImmutable) and the number of days, 14 when left out;
// - public properties book, member and due, where due is the day it was lent
//   on plus the number of days;
// - daysLate(DateTimeImmutable $today): int, how many days after the due date
//   today is, or 0 when the book isn't late.

final class Book
{
    public function __construct(public string $title) {}
}

final class Member
{
    public function __construct(public string $name) {}
}

// Check your work: composer exercise 28 advanced
