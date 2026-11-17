<?php

declare(strict_types=1);

// Exercise 65, intermediate: read a .env file.

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
            throw new UnexpectedValueException(basename($file) . ' line ' . ($number + 1) . ': expected NAME=value');
        }
        $value = $m[2];
        if (strlen($value) >= 2 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            $value = substr($value, 1, -1);
        }
        $settings[$m[1]] = $value;
    }

    return $settings;
}
