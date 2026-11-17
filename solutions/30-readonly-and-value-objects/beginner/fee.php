<?php

declare(strict_types=1);

final readonly class Fee
{
    public function __construct(public int $cents)
    {
        if ($cents < 0) {
            throw new InvalidArgumentException("A fee can't be $cents cents");
        }
    }

    public function plus(Fee $other): Fee
    {
        return new Fee($this->cents + $other->cents);
    }

    public function format(): string
    {
        return number_format($this->cents / 100, 2) . ' EUR';
    }
}
