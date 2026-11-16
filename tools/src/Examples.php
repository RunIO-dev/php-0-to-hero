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
 */
final class Examples
{
    public const NEWER = '// PHP 8.6+';

    /** @return list<string> */
    public static function check(string $root): array
    {
        $problems = [];
        $shelf = array_filter(Run::files($root . '/shelf', '.php'), static fn(string $f): bool => is_file(substr($f, 0, -4) . '.out'));
        foreach ([...Run::files($root . '/code', '.php'), ...$shelf] as $file) {
            $name = substr($file, strlen($root) + 1);
            if (in_array(basename($file), self::required(dirname($file)), true)) {
                continue; // a file of functions for its neighbours: checked through them
            }
            if (str_ends_with($file, '.bad.php')) {
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

        return $problems;
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
