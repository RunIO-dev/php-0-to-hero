<?php

declare(strict_types=1);

namespace Shelf\Report;

/** The library today, in numbers (chapter 86): the dashboard's first row. */
final readonly class Overview
{
    public function __construct(
        public int $titles,
        public int $onShelf,
        public int $onLoan,
        public int $overdue,
        public int $waiting,
        public int $setAside,
        public int $members,
    ) {}
}
