<?php

declare(strict_types=1);

abstract class LateFee
{
    final public function forDays(int $days): int
    {
        return max(0, $days) * $this->perDay();
    }

    abstract protected function perDay(): int;
}

final class BookFee extends LateFee
{
    protected function perDay(): int
    {
        return 50;
    }
}

final class DvdFee extends LateFee
{
    protected function perDay(): int
    {
        return 100;
    }
}
