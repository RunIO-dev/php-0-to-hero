<?php

declare(strict_types=1);

// Exercise 30, beginner: a fee that can't change.
//
// Write a readonly class Fee with one property, cents (an int, 0 or more; a
// negative amount is refused with an InvalidArgumentException), and methods:
// - plus(Fee $other): Fee returns a new fee with both amounts together, and
//   leaves both fees as they were;
// - format(): string, such as "1.50 EUR".
//
// Check your work: composer exercise 30 beginner
