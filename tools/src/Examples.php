<?php

declare(strict_types=1);

namespace Tools;

/**
 * Every code/**.php example runs, and prints exactly its .out file (docs/standards.md §6.1).
 * Shelf's scripts with a .out file are checked the same way.
 * A file marked "// PHP 8.6+" is skipped on older PHP. A *.bad.php file is only syntax-checked.
 * A *.error.php file shows an error on purpose: its .out holds the error message.
 * An optional .run file holds the command a learner types, with arguments and input:
 *   php greet.php Amira "Things Fall Apart" < titles.txt
 * In a Composer project (Tools\Projects), the autoloaded classes are checked through its scripts.
 * A file with a .http next to it (Part 7 on) is a web page: Tools\Web serves it and sends the requests,
 * also a *.bad.php page, to show what goes wrong (chapter 46).
 * A *Test.php file (chapter 41 on) is run with PHPUnit: its .out holds what PHPUnit prints,
 * without the lines that change from run to run.
 */
final class Examples
{
    public const NEWER = '// PHP 8.6+';

    /** @return list<string> */
    public static function check(string $root): array
    {
        $problems = Projects::prepare($root);
        $shelf = array_filter(Run::files($root . '/shelf', '.php'), static fn(string $f): bool => is_file(substr($f, 0, -4) . '.out'));
        foreach ([...Run::files($root . '/code', '.php'), ...$shelf] as $file) {
            $name = substr($file, strlen($root) + 1);
            if (in_array(basename($file), self::required(dirname($file)), true) || Projects::isAutoloaded($file) || self::isTemplate($file)
                || in_array($file, Web::served(dirname($file)), true) && !Web::has($file)) {
                continue; // loaded by its neighbours, or by the autoloader: checked through them
            }
            if (str_ends_with($file, '.bad.php') && !Web::has($file)) {
                $lint = Run::capture([PHP_BINARY, '-l', $file]);
                if ($lint['code'] !== 0) {
                    $problems[] = "$name: syntax error: " . trim($lint['out'] . $lint['err']);
                }
                continue;
            }
            if (str_contains((string) file_get_contents($file), self::NEWER) && PHP_VERSION_ID < 80600) {
                continue;
            }
            $expected = substr($file, 0, -4) . '.out';
            if (!is_file($expected)) {
                $problems[] = "$name: no .out file next to it";
                continue;
            }
            $got = self::output($root, $file);
            if ($got !== (string) file_get_contents($expected)) {
                $problems[] = "$name: output differs from its .out file:\n" . self::diff((string) file_get_contents($expected), $got);
            }
        }

        foreach ([...Run::files($root . '/code', '.http'), ...Run::files($root . '/shelf', '.http')] as $http) {
            if (is_file(substr($http, 0, -5) . '.php')) {
                continue; // a page's requests: checked with the page above
            }
            $name = substr($http, strlen($root) + 1);
            $expected = substr($http, 0, -5) . '.out';
            $got = self::siteOutput($root, $http);
            if (!is_file($expected)) {
                $problems[] = "$name: no .out file next to it";
            } elseif ($got !== (string) file_get_contents($expected)) {
                $problems[] = "$name: the answers differ from its .out file:\n" . self::diff((string) file_get_contents($expected), $got);
            }
        }

        return $problems;
    }

    /**
     * The answers to a .http file of its own, such as Shelf's web.http: the requests go to the
     * folder it is in. A Shelf snapshot is served as Shelf was after its chapter (Tools\Shelf::state).
     */
    public static function siteOutput(string $root, string $http): string
    {
        $dir = dirname($http);
        if (preg_match('~/code/\d\d-[^/]+/shelf$~', $dir) !== 1) {
            return Web::exchange($dir, (string) file_get_contents($http))['out'];
        }
        $state = sys_get_temp_dir() . '/shelf-site-' . bin2hex(random_bytes(4));
        try {
            Shelf::assemble($root, $dir, $state);
            Projects::dumpAutoload($state);

            return Web::exchange($state, (string) file_get_contents($http))['out'];
        } finally {
            Run::remove($state);
        }
    }

    /**
     * What a learner sees when running the file: output and error messages, with
     * this computer's folder removed from paths so the .out is the same everywhere.
     * Without $args, the arguments and input come from the file's .run file, if any.
     *
     * @param list<string>|null $args
     */
    public static function output(string $root, string $file, ?array $args = null, ?string $stdin = null): string
    {
        if (self::isTest($file)) {
            return self::testOutput($root, $file);
        }
        if (Web::has($file)) {
            return Web::output($file);
        }
        if ($args === null) {
            ['args' => $args, 'stdin' => $stdin] = self::command($file);
        }
        $run = Run::capture(
            [PHP_BINARY, '-d', 'display_errors=stdout', '-d', 'error_reporting=-1', '-d', 'html_errors=0', basename($file), ...$args],
            dirname($file),
            stdin: $stdin,
        );

        return str_replace(dirname($file) . '/', '', $run['out'] . $run['err']);
    }

    /**
     * How a learner runs the example: the line in its .run file, or "php <file>".
     * Only "php <file> [arguments] [< input file next to it]" is allowed; no shell runs it.
     *
     * @return array{line: string, args: list<string>, stdin: ?string, custom: bool}
     */
    public static function command(string $file): array
    {
        $name = basename($file);
        if (self::isTest($file)) {
            return ['line' => 'vendor/bin/phpunit --testdox ' . self::fromCourse($file), 'args' => [], 'stdin' => null, 'custom' => true];
        }
        if (Web::has($file)) {
            return ['line' => Web::command($file), 'args' => [], 'stdin' => null, 'custom' => true];
        }
        $runFile = substr($file, 0, -4) . '.run';
        if (!is_file($runFile)) {
            return ['line' => "php $name", 'args' => [], 'stdin' => null, 'custom' => false];
        }
        $line = trim((string) file_get_contents($runFile));
        $words = self::words($line);
        $stdin = null;
        $at = array_search('<', $words, true);
        if ($at !== false) {
            $input = $words[$at + 1] ?? '';
            if (count($words) !== $at + 2 || preg_match('~^[\w.-]+$~', $input) !== 1 || !is_file(dirname($file) . '/' . $input)) {
                throw new \RuntimeException(basename($runFile) . ': "< file" must be last and name a file in the same folder');
            }
            $stdin = dirname($file) . '/' . $input;
            $words = array_slice($words, 0, $at);
        }
        if (($words[0] ?? '') !== 'php' || ($words[1] ?? '') !== $name) {
            throw new \RuntimeException(basename($runFile) . ": the command must start with: php $name");
        }

        return ['line' => $line, 'args' => array_slice($words, 2), 'stdin' => $stdin, 'custom' => true];
    }

    /** A template (chapter 49 on): a file in a templates/ folder, checked through the pages that render it. */
    public static function isTemplate(string $file): bool
    {
        return basename(dirname($file)) === 'templates';
    }

    /**
     * The files in $dir that another file there loads with require_once __DIR__ . '/name.php'.
     *
     * @return list<string> file names
     */
    public static function required(string $dir): array
    {
        $names = [];
        foreach (glob($dir . '/*.php') ?: [] as $file) {
            preg_match_all('~\b(?:require|include)(?:_once)?\s*\(?\s*__DIR__\s*\.\s*[\'"]/([\w.-]+\.php)[\'"]~', (string) file_get_contents($file), $m);
            array_push($names, ...$m[1]);
        }

        return array_values(array_unique($names));
    }

    /**
     * A command line split at spaces, with "quoted text" kept together, as a shell would.
     *
     * @return list<string>
     */
    public static function words(string $line): array
    {
        preg_match_all('~"([^"]*)"|\'([^\']*)\'|(\S+)~', $line, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);

        return array_map(static fn(array $m): string => $m[1] ?? $m[2] ?? $m[3] ?? '', $matches);
    }

    public static function isTest(string $file): bool
    {
        return str_ends_with($file, 'Test.php');
    }

    /**
     * What PHPUnit prints for a test file, run from the course folder as a learner does,
     * without the version, runtime, configuration, time and memory lines.
     */
    private static function testOutput(string $root, string $file): string
    {
        $run = Run::capture(
            [PHP_BINARY, $root . '/vendor/bin/phpunit', '--colors=never', '--testdox', '--do-not-record-test-run-history', self::fromCourse($file)],
            $root,
        );
        $out = str_replace($root . '/', '', $run['out'] . $run['err']);
        $out = (string) preg_replace('/^(?:PHPUnit \d.*|Runtime:.*|Configuration:.*|Time: .*)\n/m', '', $out);

        return (string) preg_replace("/\n{3,}/", "\n\n", ltrim($out, "\n"));
    }

    /** The path a learner types in the course folder: code/NN-slug/... */
    private static function fromCourse(string $file): string
    {
        return preg_match('~/((?:code|shelf)/.+)$~', $file, $m) === 1 ? $m[1] : basename($file);
    }

    private static function diff(string $want, string $got): string
    {
        $w = explode("\n", $want);
        $g = explode("\n", $got);
        foreach ($w as $i => $line) {
            if (($g[$i] ?? null) !== $line) {
                return sprintf("    line %d\n    want: %s\n    got:  %s", $i + 1, $line, $g[$i] ?? '(nothing)');
            }
        }

        return sprintf("    line %d\n    want: (end)\n    got:  %s", count($w) + 1, $g[count($w)] ?? '');
    }
}
