<?php

declare(strict_types=1);

namespace Tools;

/**
 * Every code/**.php example runs, and prints exactly its .out file (docs/standards.md §6.1).
 * A file marked "// PHP 8.6+" is skipped on older PHP. A *.bad.php file is only syntax-checked.
 */
final class Examples
{
    public const NEWER = '// PHP 8.6+';

    /** @return list<string> */
    public static function check(string $root): array
    {
        $problems = [];
        foreach (Run::files($root . '/code', '.php') as $file) {
            $name = substr($file, strlen($root) + 1);
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
     */
    public static function output(string $root, string $file): string
    {
        $run = Run::capture(
            [PHP_BINARY, '-d', 'display_errors=stdout', '-d', 'error_reporting=-1', '-d', 'html_errors=0', basename($file)],
            dirname($file),
        );

        return str_replace(dirname($file) . '/', '', $run['out'] . $run['err']);
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
