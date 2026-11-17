<?php

declare(strict_types=1);

// Exercise 33, advanced: a clock you can set.
//
// Code that asks for "now" can't be tested: the answer changes every time.
// The interface Clock is given. Write:
// - SystemClock, whose now() returns the real current time;
// - FixedClock, whose constructor takes a DateTimeImmutable, and whose now()
//   always returns it;
// - a function dueDate(Clock $clock, int $days): DateTimeImmutable that
//   returns the day $days days from now, at midnight (00:00:00).

interface Clock
{
    public function now(): DateTimeImmutable;
}

// Check your work: composer exercise 33 advanced
