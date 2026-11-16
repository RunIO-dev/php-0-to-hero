<?php

declare(strict_types=1);

// Exercise 21, intermediate: how many days late?
//
// Write a function daysLate(DateTimeImmutable $dueOn, DateTimeImmutable $returnedOn): int
// that returns how many days after the due date a book came back. A book
// returned on time, or early, is 0 days late:
//   due 2026-12-01, returned 2026-12-04  ->  3
//   due 2026-12-01, returned 2026-11-28  ->  0
//
// Check your work: composer exercise 21 intermediate
