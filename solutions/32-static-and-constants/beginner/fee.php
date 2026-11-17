<?php

declare(strict_types=1);

final class LateFee
{
    public const int PER_DAY = 50;
    public const int MAXIMUM = 1000;

    public static function forDays(int $days): int
    {
        return max(0, min($days * self::PER_DAY, self::MAXIMUM));
    }
}
