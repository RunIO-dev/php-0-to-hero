<?php

declare(strict_types=1);

final class Member
{
    public function __construct(public string $name, private int $loans = 0) {}

    public function canBorrow(): bool
    {
        return $this->loans < 3;
    }
}

// json_encode sees only what code outside the class may read.
echo json_encode(new Member('Kenji', 2)), "\n";
