<?php

declare(strict_types=1);

namespace Tools;

/**
 * Chapter 40 shows what PHPStan finds in code that runs. An example X.php can have an
 * X.phpstan file: its first line is the command a learner types, the rest is what
 * PHPStan prints for it. The check runs the command and compares, so the book never
 * shows an old message. Such a file has problems on purpose, so the normal PHPStan
 * runs of tools/verify leave it out (docs/standards.md §6.4).
 */
final class Analysis
{
    public const COMMAND = '~^\$ vendor/bin/phpstan analyse --level (\d+) ((?:code|shelf)/[\w./-]+\.php)$~D';

    /** @return list<string> */
    public static function check(string $root): array
    {
        $problems = [];
        foreach (Run::files("$root/code", '.phpstan') as $expected) {
            $name = substr($expected, strlen($root) + 1);
            [$line, $want] = explode("\n", (string) file_get_contents($expected), 2) + [1 => ''];
            $command = self::parse($line);
            if ($command === null || $command['path'] !== substr($name, 0, -8) . '.php') {
                $problems[] = "$name: the first line must be: \$ vendor/bin/phpstan analyse --level <n> " . substr($name, 0, -8) . '.php';
                continue;
            }
            $got = self::output($root, $command['level'], $command['path']);
            if ($got !== $want) {
                $problems[] = "$name: PHPStan now prints something else. Update it with: php tools/out " . $command['path'] . "\n" . $got;
            }
        }

        return $problems;
    }

    /** True for an example whose PHPStan output the book shows. */
    public static function has(string $file): bool
    {
        return is_file(substr($file, 0, -4) . '.phpstan');
    }

    /** @return array{level: int, path: string}|null */
    public static function parse(string $line): ?array
    {
        return preg_match(self::COMMAND, $line, $m) === 1 ? ['level' => (int) $m[1], 'path' => $m[2]] : null;
    }

    /** What PHPStan prints for one file, run from the course folder, without this computer's folder in paths. */
    public static function output(string $root, int $level, string $path): string
    {
        $run = Run::capture(
            [PHP_BINARY, "$root/vendor/bin/phpstan", 'analyse', '--no-progress', '--no-ansi', '--level', (string) $level, $path],
            $root,
        );

        return str_replace($root . '/', '', $run['out'] . $run['err']);
    }

    /** Writes the .phpstan file of an example again, with the command already in it. */
    public static function write(string $root, string $file): void
    {
        $expected = substr($file, 0, -4) . '.phpstan';
        $line = strtok((string) file_get_contents($expected), "\n");
        $command = self::parse((string) $line) ?? throw new \RuntimeException("$expected: first line is not a PHPStan command");
        file_put_contents($expected, $line . "\n" . self::output($root, $command['level'], $command['path']));
    }
}
