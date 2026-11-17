<?php

declare(strict_types=1);

final readonly class Money
{
    private function __construct(public int $cents) {}

    public static function zero(): self
    {
        return new self(0);
    }
}

$fee = new Money(150);
