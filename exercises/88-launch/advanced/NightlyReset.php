<?php

declare(strict_types=1);

// When the demo resets (chapter 88): every night at the same time in one time zone, by default 03:00
// UTC. Shelf could show it: "it all goes back in 5 hours".
// - next($now): the first reset after $now, in the reset's time zone; a reset exactly at $now is over.
// - until($now): how long until then, in words: "in 5 hours", "in 1 hour", "in 45 minutes",
//   "in 1 minute". Whole hours (rounded down) from one hour on; below that, minutes, rounded up,
//   so it never says "in 0 minutes".
// $now may be in any time zone: 04:30 in Berlin, in summer, is 02:30 UTC. And in a zone with summer
// time, the night the clocks go forward is an hour shorter.

final readonly class NightlyReset
{
    public function __construct(private string $at = '03:00', private string $zone = 'UTC') {}

    public function next(DateTimeImmutable $now): DateTimeImmutable
    {
        return $now; // TODO
    }

    public function until(DateTimeImmutable $now): string
    {
        return ''; // TODO
    }
}
