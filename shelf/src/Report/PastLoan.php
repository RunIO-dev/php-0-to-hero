<?php

declare(strict_types=1);

namespace Shelf\Report;

use DateTimeImmutable;

/** One line of the loan history (chapter 86): a row of a report, not an entity. Nothing here changes. */
final readonly class PastLoan
{
    public function __construct(
        public string $title,
        public string $member,
        public DateTimeImmutable $lentOn,
        public DateTimeImmutable $due,
        public ?DateTimeImmutable $returnedOn,
    ) {}

    /** How many days after the due date it came back, or is still out; 0 if on time. */
    public function daysLate(DateTimeImmutable $today): int
    {
        $end = new DateTimeImmutable(($this->returnedOn ?? $today)->format('Y-m-d'));
        $late = (int) $this->due->diff($end)->format('%r%a');

        return max(0, $late);
    }
}
