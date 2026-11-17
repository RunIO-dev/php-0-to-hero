<?php

declare(strict_types=1);

final class Member
{
    public string $name;
    public int $loans = 0;

    public function canBorrow(): bool
    {
        return $this->loans < 3;
    }
}
