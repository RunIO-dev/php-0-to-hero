<?php

declare(strict_types=1);

// Reading a .env file: one NAME=value per line; empty lines and lines starting with # are skipped.

/** @return array<string, string> */
function readEnvFile(string $file): array
{
    $settings = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $number => $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (preg_match('/^([A-Z][A-Z0-9_]*)=(.*)$/', $line, $m) !== 1) {
            throw new UnexpectedValueException(basename($file) . ' line ' . ($number + 1) . ": expected NAME=value: $line");
        }
        $settings[$m[1]] = trim($m[2], '"'); // quotes are allowed around a value, and dropped
    }

    return $settings;
}
