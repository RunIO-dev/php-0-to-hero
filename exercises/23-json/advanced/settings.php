<?php

declare(strict_types=1);

// Exercise 23, advanced: the library's settings.
//
// A JSON file holds settings such as {"loanDays": 21, "feePerDay": 30}.
// Write a function loadSettings(string $path): array that returns
//   ['loanDays' => int, 'feePerDay' => int]
// - A setting that is missing gets its default: loanDays 14, feePerDay 50.
// - A setting that is there, but not a whole number, throws an
//   UnexpectedValueException that names the setting.
// - Broken JSON throws a JsonException; a missing file a RuntimeException.
//
// Check your work: composer exercise 23 advanced
