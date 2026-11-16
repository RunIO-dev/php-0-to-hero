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

/**
 * What a script prints (and its error messages), run in its own PHP process,
 * with command-line arguments and typed input, as in: php file.php add Kindred
 *
 * @param list<string> $args
 */
function output_of(string $file, array $args = [], ?string $input = null): string
{
    if ($input === null) {
        return Tools\Examples::output(dirname($file), $file, $args);
    }
    $stdin = (string) tempnam(sys_get_temp_dir(), 'input-');
    try {
        file_put_contents($stdin, $input);

        return Tools\Examples::output(dirname($file), $file, $args, $stdin);
    } finally {
        unlink($stdin);
    }
}

/**
 * What a script prints when it starts with other values: the first assignment
 * `$name = ...;` of each given variable (it may span lines) becomes `$name = <code>;`.
 * Tests use it to try a learner's script with several inputs. Not supported: a string
 * value with a ; at the end of one of its lines.
 *
 * @param array<string, string> $values variable name (without $) => PHP code for its value
 */
function output_with(string $file, array $values): string
{
    $code = (string) file_get_contents($file);
    foreach ($values as $name => $value) {
        // up to the first ; that ends a line, or is followed only by a // or # comment
        $pattern = '/^\$' . preg_quote($name, '/') . ' = .*?;[ \t]*(?:(?:\/\/|#)[^\n]*)?$/ms';
        if (preg_match($pattern, $code) !== 1) {
            throw new RuntimeException("Keep the line \$$name = ...; at the start of " . basename($file) . ', the tests change its value.');
        }
        $code = (string) preg_replace($pattern, '$' . $name . ' = ' . addcslashes($value, '\\$') . ';', $code, 1);
    }
    $dir = sys_get_temp_dir() . '/output-with-' . bin2hex(random_bytes(4));
    mkdir($dir);
    $copy = $dir . '/' . basename($file);
    try {
        file_put_contents($copy, $code);

        return output_of($copy);
    } finally {
        Tools\Run::remove($dir);
    }
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
