<?php

declare(strict_types=1);

// Exercise 65, advanced: check every setting at the start, and name every problem at once.
//
// LibraryConfig::fromSettings($settings) makes the library's settings from [NAME => value]:
// - LIBRARY_NAME: required, any text that isn't empty;
// - LOAN_DAYS: required, a whole number from 1 to 90;
// - LIBRARY_DEBUG: "yes" or "no", "no" when missing; becomes true or false.
// If anything is wrong, throw one UnexpectedValueException whose message lists every problem,
// one per line, in the order above, exactly as:
//   "LIBRARY_NAME is missing"
//   "LOAN_DAYS must be a whole number from 1 to 90"
//   "LIBRARY_DEBUG must be yes or no"

final readonly class LibraryConfig
{
    public function __construct(public string $name, public int $loanDays, public bool $debug) {}

    /** @param array<string, string> $settings */
    public static function fromSettings(array $settings): self
    {
        return new self($settings['LIBRARY_NAME'] ?? '', (int) ($settings['LOAN_DAYS'] ?? 0), ($settings['LIBRARY_DEBUG'] ?? '') === 'yes');
    }
}
// Check your work: composer exercise 65 advanced
