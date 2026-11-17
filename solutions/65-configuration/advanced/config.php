<?php

declare(strict_types=1);

// Exercise 65, advanced: check every setting at the start, and name every problem at once.

final readonly class LibraryConfig
{
    public function __construct(public string $name, public int $loanDays, public bool $debug) {}

    /** @param array<string, string> $settings */
    public static function fromSettings(array $settings): self
    {
        $problems = [];
        $name = trim($settings['LIBRARY_NAME'] ?? '');
        if ($name === '') {
            $problems[] = 'LIBRARY_NAME is missing';
        }
        $days = filter_var($settings['LOAN_DAYS'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 90]]);
        if ($days === false) {
            $problems[] = 'LOAN_DAYS must be a whole number from 1 to 90';
        }
        $debug = $settings['LIBRARY_DEBUG'] ?? 'no';
        if (!in_array($debug, ['yes', 'no'], true)) {
            $problems[] = 'LIBRARY_DEBUG must be yes or no';
        }
        if ($problems !== [] || $days === false) {
            throw new UnexpectedValueException(implode("\n", $problems));
        }

        return new self($name, $days, $debug === 'yes');
    }
}
