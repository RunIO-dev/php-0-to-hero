<?php

declare(strict_types=1);

// Exercise 58, intermediate: which migrations still have to run?

/**
 * @param list<string> $files
 * @param list<string> $ran
 *
 * @return list<string>
 */
function pending(array $files, array $ran): array
{
    $numbers = [];
    foreach ($files as $file) {
        if (preg_match('/^(\d{3})-[a-z0-9-]+\.sql$/', $file, $m) !== 1) {
            throw new RuntimeException("Name migrations like 004-what-it-does.sql: $file");
        }
        if (isset($numbers[$m[1]])) {
            throw new RuntimeException("Two migrations have the number {$m[1]}: {$numbers[$m[1]]} and $file");
        }
        $numbers[$m[1]] = $file;
    }
    foreach ($ran as $name) {
        if (!in_array($name, $files, true)) {
            throw new RuntimeException("$name ran, but its file is gone");
        }
    }
    ksort($numbers);

    return array_values(array_diff($numbers, $ran));
}
