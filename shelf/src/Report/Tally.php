<?php

declare(strict_types=1);

namespace Shelf\Report;

/** One line of a count (chapter 86): a book and how often it was lent, or a month and its loans. */
final readonly class Tally
{
    public function __construct(public string $label, public int $count) {}
}
