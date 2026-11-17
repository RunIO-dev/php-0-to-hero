<?php

declare(strict_types=1);

// Exercise 30, intermediate: a loan period.
//
// Write a readonly class LoanPeriod with two DateTimeImmutable properties,
// from and until. until must be after from, or the constructor throws an
// InvalidArgumentException. Methods:
// - days(): int, the number of days from from to until;
// - extendedBy(int $days): LoanPeriod, a new period that ends $days later;
// - contains(DateTimeImmutable $day): bool, true from from to until, both
//   days included.
//
// Check your work: composer exercise 30 intermediate
