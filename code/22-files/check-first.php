<?php

declare(strict_types=1);

/** @return list<string> the non-empty lines of a text file */
function readLines(string $path): array
{
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException("Cannot read $path");
    }

    return file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
}

echo count(readLines(__DIR__ . '/titles.txt')), " titles\n";

try {
    readLines(__DIR__ . '/members.txt');
} catch (RuntimeException $e) {
    echo $e->getMessage(), "\n";
}
