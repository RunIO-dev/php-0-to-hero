<?php

declare(strict_types=1);

// Exercise 21, advanced: is this a real date?
//
// A member types a date. Write a function isValidDate(string $input): bool
// that accepts only a real date written exactly as YYYY-MM-DD:
//   '2026-11-17'  true        '2028-02-29'  true (a leap year)
//   '2026-02-30'  false       '2026-2-3'    false
//   'tomorrow'    false       ''            false
// new DateTimeImmutable() is not enough: it accepts '2026-02-30' and 'tomorrow'.
//
// Check your work: composer exercise 21 advanced
