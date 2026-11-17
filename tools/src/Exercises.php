<?php

declare(strict_types=1);

namespace Tools;

/**
 * Every exercise is real: its tests fail on the starter and pass on the solution
 * (docs/standards.md §5).
 */
final class Exercises
{
    public const LEVELS = ['beginner', 'intermediate', 'advanced'];

    /** @return list<string> */
    public static function check(string $root): array
    {
        $problems = [];
        foreach (self::all($root) as $dir) {
            $name = substr($dir, strlen($root) + 1);
            $solution = $root . '/solutions/' . substr($dir, strlen($root . '/exercises/'));
            if (Run::files($dir, 'Test.php') === []) {
                $problems[] = "$name: no *Test.php";
                continue;
            }
            if (!is_dir($solution)) {
                $problems[] = "$name: no solution in " . substr($solution, strlen($root) + 1);
                continue;
            }
            if ((Database::isNeeded($dir) || Database::isNeeded(dirname($dir))) && !Database::isUp()) {
                $problems[] = "$name: " . Database::START;
                continue;
            }
            $starter = self::test($root, $dir, false);
            if ($starter['code'] === 0) {
                $problems[] = "$name: the tests pass on the starter, so the exercise asks for nothing";
            } elseif (preg_match('/^(FAILURES|ERRORS)!$/m', $starter['out']) !== 1) {
                $problems[] = "$name: the starter must fail its tests, not break PHPUnit:\n" . trim($starter['out'] . $starter['err']);
            }
            $run = self::test($root, $dir, true);
            if ($run['code'] !== 0) {
                $problems[] = "$name: the tests fail on the solution:\n" . trim($run['out']);
            }
        }

        return $problems;
    }

    /** @return list<string> exercises/NN-slug/<level> folders */
    public static function all(string $root): array
    {
        $dirs = [];
        foreach (glob($root . '/exercises/*', GLOB_ONLYDIR) ?: [] as $chapter) {
            foreach (glob($chapter . '/*', GLOB_ONLYDIR) ?: [] as $level) {
                $dirs[] = $level;
            }
        }

        return $dirs;
    }

    /** @return array{code: int, out: string, err: string} */
    public static function test(string $root, string $dir, bool $solution): array
    {
        return Run::capture(
            [PHP_BINARY, $root . '/vendor/bin/phpunit', '-c', $root . '/phpunit.xml.dist',
                '--do-not-record-test-run-history', '--colors=never', $dir],
            $root,
            ['EXERCISE_SOLUTION' => $solution ? '1' : '0'],
        );
    }
}
