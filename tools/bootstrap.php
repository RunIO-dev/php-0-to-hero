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

/**
 * What PHPStan says about a file at a level, run from the course folder (chapter 40 on).
 *
 * @return array{passed: bool, out: string}
 */
function analysis_of(string $file, int $level): array
{
    $root = dirname(__DIR__);
    $run = Tools\Run::capture(
        [PHP_BINARY, "$root/vendor/bin/phpstan", 'analyse', '--no-progress', '--no-ansi', '--error-format=raw', '--level', (string) $level, $file],
        $root,
    );

    return ['passed' => $run['code'] === 0, 'out' => trim(str_replace($root . '/', '', $run['out'] . $run['err']))];
}

/**
 * Runs a test file against another version of the code it tests (chapter 41 on): the
 * test is copied into an empty folder, next to $code saved as $subject. Exercises in
 * which you write the tests use it to check that your tests catch each bug in bugs/.
 *
 * @return array{passed: bool, out: string}
 */
function tests_against(string $testFile, string $subject, string $code): array
{
    $root = dirname(__DIR__);
    $dir = sys_get_temp_dir() . '/tests-against-' . bin2hex(random_bytes(4));
    mkdir($dir);
    try {
        copy($testFile, $dir . '/' . basename($testFile));
        file_put_contents("$dir/$subject", $code);
        $run = Tools\Run::capture([PHP_BINARY, "$root/vendor/bin/phpunit", '-c', "$root/phpunit.xml.dist",
            '--colors=never', '--do-not-record-test-run-history', $dir . '/' . basename($testFile)], $root);

        return ['passed' => $run['code'] === 0, 'out' => str_replace("$dir/", '', $run['out'] . $run['err'])];
    } finally {
        Tools\Run::remove($dir);
    }
}

/** What a file in bugs/ gets wrong: its "// Bug:" line, or else its first PHPDoc comment. */
function bug_of(string $code): string
{
    if (preg_match('~^// Bug: (.+)$~m', $code, $m) === 1 || preg_match('~/\*\*\s*(.+?)\s*\*/~s', $code, $m) === 1) {
        return $m[1];
    }

    return 'a bug';
}

/**
 * Checks that the tests you wrote pass on the right code and fail on every file in bugs/.
 *
 * @return list<string> what is missing, empty when your tests are good
 */
function missed_bugs(string $exerciseDir, string $testFile, string $subject): array
{
    $missed = [];
    $right = tests_against(subject($exerciseDir, $testFile), $subject, (string) file_get_contents("$exerciseDir/$subject"));
    if (!$right['passed']) {
        $missed[] = "Your tests must pass on $subject, which is right:\n" . $right['out'];
    }
    foreach (glob("$exerciseDir/bugs/*.php") ?: [] as $bug) {
        if (tests_against(subject($exerciseDir, $testFile), $subject, (string) file_get_contents($bug))['passed']) {
            $missed[] = 'Your tests pass on bugs/' . basename($bug) . ': ' . bug_of((string) file_get_contents($bug));
        }
    }

    return $missed;
}
