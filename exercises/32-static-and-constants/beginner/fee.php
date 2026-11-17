<?php

declare(strict_types=1);

// Exercise 32, beginner: the late fee, with named numbers.
//
// Write a class LateFee with two public int constants: PER_DAY (50 cents)
// and MAXIMUM (1000 cents). Add a static method forDays(int $days): int that
// returns the fee for that many days late: PER_DAY for each day, never more
// than MAXIMUM, and 0 for 0 days or fewer.
//
// Check your work: composer exercise 32 beginner
