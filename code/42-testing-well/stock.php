<?php

declare(strict_types=1);

final class NoCopyLeft extends RuntimeException {}

/** The copies of one book that are on the shelf. */
final class Stock
{
    public function __construct(public private(set) int $copies) {}

    public function lend(): void
    {
        if ($this->copies === 0) {
            throw new NoCopyLeft('All copies are on loan');
        }
        $this->copies--;
    }
}
