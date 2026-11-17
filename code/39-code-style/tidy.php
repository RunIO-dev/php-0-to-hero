<?php

declare(strict_types=1);

// The same code as messy.php, in PER Coding Style. It prints the same.

final class LateFee
{
    public const int CENTS_PER_DAY = 20;

    public function forDays(int $days): int
    {
        if ($days <= 0) {
            return 0;
        }

        return $days * self::CENTS_PER_DAY;
    }
}

echo (new LateFee())->forDays(3), "\n";
