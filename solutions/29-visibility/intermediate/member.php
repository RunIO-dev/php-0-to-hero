<?php

declare(strict_types=1);

final class Member
{
    /** @var list<string> */
    private array $titles = [];

    public function __construct(public string $name) {}

    public function borrow(string $title): void
    {
        if (count($this->titles) === 3) {
            throw new RuntimeException("$this->name already has 3 books");
        }
        $this->titles[] = $title;
    }

    public function giveBack(string $title): void
    {
        $position = array_search($title, $this->titles, true);
        if ($position === false) {
            throw new RuntimeException("$this->name hasn't borrowed \"$title\"");
        }
        array_splice($this->titles, $position, 1);
    }

    /** @return list<string> */
    public function titles(): array
    {
        return $this->titles;
    }
}
