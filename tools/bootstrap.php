<?php

declare(strict_types=1);

// Loaded before every test: our tools, and the subject() helper for exercises.
require __DIR__ . '/../vendor/autoload.php';

/**
 * The file an exercise test checks: your starter file, or the solution when
 * tools/verify runs the test with EXERCISE_SOLUTION=1.
 * Use it in a test as: require_once subject(__DIR__, 'shelf.php');
 */
function subject(string $testDir, string $file): string
{
    $dir = getenv('EXERCISE_SOLUTION') === '1'
        ? (string) preg_replace('~/exercises/([^/]+/[^/]+)$~', '/solutions/$1', $testDir)
        : $testDir;

    return $dir . '/' . $file;
}
