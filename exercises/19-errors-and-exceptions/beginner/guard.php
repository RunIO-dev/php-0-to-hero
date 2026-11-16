<?php

declare(strict_types=1);

// Exercise 19, beginner: fail fast.
//
// pagesPerDay(320, 0) can't give a sensible answer. Make the function check
// its input first, and throw an InvalidArgumentException when $days is
// below 1, with a message that says the value it got:
//   Reading takes at least 1 day, not 0
//
// Check your work: composer exercise 19 beginner

function pagesPerDay(int $pages, int $days): int
{
    return (int) ceil($pages / $days);
}
