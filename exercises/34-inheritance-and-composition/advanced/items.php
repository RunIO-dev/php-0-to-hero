<?php

declare(strict_types=1);

// Exercise 34, advanced: items on loan.
//
// The library lends books for 14 days and DVDs for 7. Item is given. Write:
// - a class Dvd that extends Item: its constructor takes a title and the
//   running time in minutes (a public readonly int minutes); loanDays()
//   returns 7; label() returns Item's label followed by " (DVD)". Use
//   parent:: for both the constructor and the label, and mark the methods you
//   override with #[Override]. No class may extend Dvd.
// - a function dueDates(array $items, DateTimeImmutable $today): array that
//   returns a map from each title to its due date, as "Y-m-d", using each
//   item's own loanDays().

class Item
{
    public readonly string $title;

    public function __construct(string $title)
    {
        $this->title = trim($title);
    }

    public function loanDays(): int
    {
        return 14;
    }

    public function label(): string
    {
        return "\"$this->title\"";
    }
}

// Check your work: composer exercise 34 advanced
