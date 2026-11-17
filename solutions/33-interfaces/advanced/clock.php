<?php

declare(strict_types=1);

interface Clock
{
    public function now(): DateTimeImmutable;
}

final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}

final readonly class FixedClock implements Clock
{
    public function __construct(private DateTimeImmutable $now) {}

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}

function dueDate(Clock $clock, int $days): DateTimeImmutable
{
    return $clock->now()->setTime(0, 0)->modify("+$days days");
}
