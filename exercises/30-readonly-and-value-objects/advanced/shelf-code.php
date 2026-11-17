<?php

declare(strict_types=1);

// Exercise 30, advanced: a shelf code as a value object.
//
// A shelf code is two capital letters, a hyphen and three digits: SF-012.
// Write a readonly class ShelfCode:
// - the constructor accepts the code with spaces around it and in small
//   letters too ("  sf-012 "), and keeps it as "SF-012" in a public property
//   code. Anything else is refused with an InvalidArgumentException.
// - section(): string returns the letters ("SF");
// - number(): int returns the number (12);
// - equals(ShelfCode $other): bool is true when both are the same code.
//
// Check your work: composer exercise 30 advanced
