<?php

declare(strict_types=1);

final class Book
{
    public private(set) int $copies;

    public function __construct(public private(set) string $title, private int $owned)
    {
        if ($owned < 1) {
            throw new InvalidArgumentException('The library owns at least 1 copy');
        }
        $this->copies = $owned;
    }

    public function lend(): void
    {
        if ($this->copies === 0) {
            throw new RuntimeException("All copies of \"$this->title\" are on loan");
        }
        $this->copies--;
    }

    public function giveBack(): void
    {
        if ($this->copies === $this->owned) {
            throw new RuntimeException("No copy of \"$this->title\" is on loan");
        }
        $this->copies++;
    }

    public function onLoan(): int
    {
        return $this->owned - $this->copies;
    }
}
