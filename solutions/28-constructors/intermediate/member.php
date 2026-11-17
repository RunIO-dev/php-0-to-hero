<?php

declare(strict_types=1);

final class Member
{
    public string $name;

    public function __construct(string $name, public int $maxLoans = 3)
    {
        $this->name = trim($name);
        if ($this->name === '') {
            throw new InvalidArgumentException('A member needs a name');
        }
        if ($maxLoans < 1 || $maxLoans > 10) {
            throw new InvalidArgumentException('A member may borrow 1 to 10 books');
        }
    }
}
