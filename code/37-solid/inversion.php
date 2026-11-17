<?php

declare(strict_types=1);

// D: the rule depends on an interface it defines, not on a concrete clock.
interface Clock
{
    public function today(): DateTimeImmutable;
}

final readonly class FixedClock implements Clock
{
    public function __construct(private DateTimeImmutable $day) {}

    public function today(): DateTimeImmutable
    {
        return $this->day;
    }
}

final readonly class Overdue
{
    public function __construct(private Clock $clock) {}

    public function isLate(DateTimeImmutable $due): bool
    {
        return $this->clock->today() > $due;
    }
}

$overdue = new Overdue(new FixedClock(new DateTimeImmutable('2026-12-02')));

var_dump($overdue->isLate(new DateTimeImmutable('2026-11-30')));
var_dump($overdue->isLate(new DateTimeImmutable('2026-12-14')));
