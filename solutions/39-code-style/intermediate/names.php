<?php

declare(strict_types=1);

// Exercise 39, intermediate: names as PSR-1 asks for them. A solution.

final class LateFeeCalculator
{
    public const int CENTS_PER_DAY = 20;
    public const int MAX_FINE = 500;

    public function calculate(int $days): int
    {
        return $this->isFree($days) ? 0 : min($days * self::CENTS_PER_DAY, self::MAX_FINE);
    }

    public function isFree(int $days): bool
    {
        return $days <= 0;
    }
}
