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

/** What a script prints (and its error messages), run in its own PHP process. */
function output_of(string $file): string
{
    return Tools\Examples::output(dirname($file), $file);
}

/**
 * The variable names a script uses, without the $, in order of first use.
 *
 * @return list<string>
 */
function variables_in(string $file): array
{
    $names = [];
    foreach (PhpToken::tokenize((string) file_get_contents($file)) as $token) {
        if ($token->is(T_VARIABLE)) {
            $names[] = substr($token->text, 1);
        }
    }

    return array_values(array_unique($names));
}

/**
 * The text values a script writes in quotes, such as 'Frankenstein'.
 *
 * @return list<string>
 */
function strings_in(string $file): array
{
    $values = [];
    foreach (PhpToken::tokenize((string) file_get_contents($file)) as $token) {
        if ($token->is(T_CONSTANT_ENCAPSED_STRING)) {
            $values[] = stripcslashes(substr($token->text, 1, -1));
        }
    }

    return $values;
}
