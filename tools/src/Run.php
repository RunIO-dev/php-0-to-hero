<?php

declare(strict_types=1);

namespace Tools;

/** Runs a command without a shell, so no argument is ever interpreted. */
final class Run
{
    /**
     * Runs $command with no input (or the file $stdin as input) and a time limit, so
     * an example that waits for input or loops forever fails the check instead of hanging it.
     *
     * @param list<string> $command
     * @param array<string, string> $env extra environment variables
     * @return array{code: int, out: string, err: string}
     */
    public static function capture(array $command, ?string $cwd = null, array $env = [], int $seconds = 120, ?string $stdin = null): array
    {
        $process = proc_open(
            $command,
            [0 => ['file', $stdin ?? '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $cwd,
            $env === [] ? null : array_merge(getenv(), $env),
        );
        if ($process === false) {
            throw new \RuntimeException('Could not start: ' . implode(' ', $command));
        }
        $text = [1 => '', 2 => ''];
        $open = [1 => $pipes[1], 2 => $pipes[2]];
        $deadline = microtime(true) + $seconds;
        // Read both pipes as data arrives, so a full stderr can never block stdout.
        while ($open !== [] && microtime(true) < $deadline) {
            $read = array_values($open);
            $write = $except = null;
            if (stream_select($read, $write, $except, 1) === false) {
                break;
            }
            foreach ($open as $n => $pipe) {
                if (in_array($pipe, $read, true)) {
                    $chunk = (string) fread($pipe, 65536);
                    $text[$n] .= $chunk;
                    if ($chunk === '' && feof($pipe)) {
                        fclose($pipe);
                        unset($open[$n]);
                    }
                }
            }
        }
        if ($open !== []) {
            proc_terminate($process, 9);
            array_map('fclose', $open);
            proc_close($process);

            return ['code' => 124, 'out' => $text[1], 'err' => $text[2] . "\nStopped after $seconds seconds (waiting for input, or an endless loop?)"];
        }

        return ['code' => proc_close($process), 'out' => $text[1], 'err' => $text[2]];
    }

    /** @return list<string> every file under $dir whose name ends in $suffix, sorted, without vendor/ */
    public static function files(string $dir, string $suffix): array
    {
        if (!is_dir($dir)) {
            return [];
        }
        $found = [];
        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($items as $item) {
            $path = (string) $item;
            $inside = substr($path, strlen($dir));
            if (str_ends_with($path, $suffix) && !str_contains($inside, '/vendor/')) {
                $found[] = $path;
            }
        }
        sort($found);

        return $found;
    }

    /** Deletes $dir and everything in it, without following symbolic links. */
    public static function remove(string $dir): void
    {
        if (is_link($dir) || !is_dir($dir)) {
            if (file_exists($dir) || is_link($dir)) {
                unlink($dir);
            }

            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($items as $item) {
            $path = (string) $item;
            is_dir($path) && !is_link($path) ? rmdir($path) : unlink($path);
        }
        rmdir($dir);
    }
}
