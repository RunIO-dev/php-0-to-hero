<?php

declare(strict_types=1);

namespace Tools;

/**
 * From chapter 36, Shelf is a Composer project of its own: a folder with a
 * composer.json whose classes, in a folder such as src/, Composer's autoloader
 * loads. Only the scripts next to composer.json run; the classes are checked
 * through them. Composer writes the autoloader into vendor/, which git ignores,
 * so the checks write it before they run anything (docs/standards.md §4).
 * From chapter 40, a project can have its own phpstan.neon.dist (its own level), and from
 * chapter 41 its own tests, with phpunit.xml.dist. The checks use them as a learner does.
 */
final class Projects
{
    /** The folder of the Composer project that $file belongs to, or null. The course's own composer.json doesn't count. */
    public static function of(string $file): ?string
    {
        for ($dir = dirname($file); dirname($dir) !== $dir; $dir = dirname($dir)) {
            if (is_file("$dir/tools/verify")) {
                return null;
            }
            if (is_file("$dir/composer.json")) {
                return $dir;
            }
        }

        return null;
    }

    /**
     * True for a file that the autoloader loads: inside a project, below the folder of its
     * composer.json. A Shelf snapshot counts as the project it is part of, even when its
     * chapter didn't change composer.json.
     */
    public static function isAutoloaded(string $file): bool
    {
        $snapshot = self::snapshotOf($file);
        if ($snapshot !== null) {
            return dirname($file) !== $snapshot[1] && isset(Shelf::state($snapshot[0], $snapshot[1])['composer.json']);
        }
        $project = self::of($file);

        return $project !== null && dirname($file) !== $project;
    }

    /** @return array{string, string}|null the course folder and the code/NN-slug/shelf folder that $file is in */
    private static function snapshotOf(string $file): ?array
    {
        return preg_match('~^(.*)/code/\d\d-[^/]+/shelf(?=/)~', $file, $m) === 1 ? [$m[1], $m[0]] : null;
    }

    /**
     * Writes the autoloader of every project in code/ and shelf/.
     *
     * @return list<string> problems
     */
    public static function prepare(string $root): array
    {
        $problems = [];
        foreach (self::all($root) as $dir) {
            $problem = self::dumpAutoload($dir);
            if ($problem !== null) {
                $problems[] = substr($dir, strlen($root) + 1) . ": $problem";
            }
        }

        return $problems;
    }

    /** @return list<string> the folder of every project in code/ and shelf/ */
    public static function all(string $root): array
    {
        $files = [...Run::files("$root/code", '/composer.json'), ...Run::files("$root/shelf", '/composer.json')];

        return array_map(dirname(...), $files);
    }

    /**
     * True for a file that PHPStan checks with its project's own configuration: shelf/ with a
     * phpstan.neon.dist, or a Shelf snapshot of a chapter by which Shelf has one.
     */
    public static function hasOwnAnalysis(string $file): bool
    {
        // a snapshot holds only what its chapter changes, so it may have no composer.json of its own
        $snapshot = self::snapshotOf($file);
        if ($snapshot !== null) {
            return isset(Shelf::state($snapshot[0], $snapshot[1])['phpstan.neon.dist']);
        }
        $project = self::of($file);

        return $project !== null && is_file("$project/phpstan.neon.dist");
    }

    /**
     * Runs PHPStan with Shelf's own configuration (chapter 40 on), in shelf/ and in the state
     * after every chapter snapshot that has one.
     *
     * @return list<string> problems
     */
    public static function analyse(string $root): array
    {
        return self::inEachShelf($root, 'phpstan.neon.dist', static fn(string $dir): array => Run::capture(
            [PHP_BINARY, "$root/vendor/bin/phpstan", 'analyse', '--no-progress', '--error-format=raw', '-c', "$dir/phpstan.neon.dist"],
            $root,
        ));
    }

    /**
     * Runs Shelf's tests (chapter 41 on) with the course's PHPUnit, in shelf/ and in the
     * state after every chapter snapshot that has them.
     *
     * @return list<string> problems
     */
    public static function test(string $root): array
    {
        return self::inEachShelf($root, 'phpunit.xml.dist', static fn(string $dir): array => Run::capture(
            [PHP_BINARY, "$root/vendor/bin/phpunit", '-c', $dir, '--colors=never', '--do-not-record-test-run-history'],
            $root,
        ));
    }

    /**
     * @param callable(string): array{code: int, out: string, err: string} $run
     * @return list<string> problems
     */
    private static function inEachShelf(string $root, string $config, callable $run): array
    {
        $problems = [];
        $report = static function (string $name, array $result) use (&$problems): void {
            if ($result['code'] !== 0) {
                $problems[] = "$name: " . trim($result['out'] . $result['err']);
            }
        };
        foreach (glob("$root/code/[0-9][0-9]-*/shelf", GLOB_ONLYDIR) ?: [] as $snapshot) {
            if (!isset(Shelf::state($root, $snapshot)[$config])) {
                continue;
            }
            $dir = sys_get_temp_dir() . '/shelf-' . basename(dirname($snapshot)) . '-' . bin2hex(random_bytes(4));
            try {
                Shelf::assemble($root, $snapshot, $dir);
                $problem = self::dumpAutoload($dir);
                $name = substr($snapshot, strlen($root) + 1);
                $problem === null ? $report($name, $run($dir)) : $problems[] = "$name: $problem";
            } finally {
                Run::remove($dir);
            }
        }
        if (is_file("$root/shelf/$config")) {
            $report('shelf', $run("$root/shelf"));
        }

        return $problems;
    }

    public static function dumpAutoload(string $dir): ?string
    {
        $composer = getenv('COMPOSER_BINARY');
        $command = is_string($composer) && $composer !== '' ? [PHP_BINARY, $composer] : ['composer'];
        // --dev: also the autoload-dev rules (chapter 41), which Composer leaves out when no dev packages were installed
        $run = Run::capture([...$command, 'dump-autoload', '--dev', '--quiet', '--no-interaction'], $dir);

        return $run['code'] === 0 ? null : 'composer dump-autoload failed: ' . trim($run['out'] . $run['err']);
    }
}
