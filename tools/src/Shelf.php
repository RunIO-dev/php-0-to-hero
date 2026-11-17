<?php

declare(strict_types=1);

namespace Tools;

/**
 * Shelf grows chapter by chapter. A chapter that changes it keeps its version in
 * code/NN-slug/shelf/, and the book includes that snapshot, so an early chapter
 * never shows code from a later one. shelf/ is the newest state: every file in it
 * must equal its newest snapshot, and every file of the newest state must be in shelf/
 * (docs/standards.md §1).
 */
final class Shelf
{
    /** An empty file X.removed in a snapshot: from that chapter on, Shelf has no file X. */
    public const string REMOVED = '.removed';

    /** @return list<string> */
    public static function check(string $root): array
    {
        $problems = [];
        foreach (Run::files($root . '/shelf', '') as $file) {
            $path = substr($file, strlen($root . '/shelf/'));
            if ($path === 'README.md' || str_starts_with($path, '.phpunit.cache/')) {
                continue; // about Shelf, not part of it; or PHPUnit's cache (chapter 41)
            }
            $snapshots = glob("$root/code/[0-9][0-9]-*/shelf/{" . "$path,$path" . self::REMOVED . '}', GLOB_BRACE) ?: [];
            if ($snapshots === []) {
                $problems[] = "shelf/$path: no snapshot in code/NN-slug/shelf/ (copy it into the chapter that changes it)";
                continue;
            }
            usort($snapshots, static fn(string $a, string $b): int => strcmp(dirname($a), dirname($b)) ?: strcmp($a, $b));
            $newest = (string) end($snapshots);
            if (str_ends_with($newest, self::REMOVED)) {
                $problems[] = "shelf/$path was removed in " . substr($newest, strlen($root) + 1) . '; delete it';
            } elseif (file_get_contents($newest) !== file_get_contents($file)) {
                $problems[] = "shelf/$path differs from its newest snapshot " . substr($newest, strlen($root) + 1);
            }
        }
        $dirs = glob("$root/code/[0-9][0-9]-*/shelf", GLOB_ONLYDIR) ?: [];
        sort($dirs);
        foreach ($dirs === [] ? [] : self::state($root, (string) end($dirs)) as $path => $snapshot) {
            if (!is_file("$root/shelf/$path")) {
                $problems[] = "shelf/$path is missing; its newest snapshot is " . substr($snapshot, strlen($root) + 1);
            }
        }
        foreach (glob($root . '/book/*/*.md') ?: [] as $chapter) {
            preg_match_all(Chapters::INCLUDE, (string) file_get_contents($chapter), $m);
            foreach ($m[1] as $include) {
                if (str_starts_with($include, 'shelf/')) {
                    $problems[] = substr($chapter, strlen($root) + 1) . ": include the chapter's snapshot code/NN-slug/$include, not $include";
                }
            }
        }

        return $problems;
    }

    /**
     * Shelf as it is after the chapter of a snapshot: every file of that snapshot and of the
     * snapshots before it, each in its newest version up to that chapter. A snapshot holds only
     * what its chapter changes, so this is what the chapter's tests and analysis run on.
     *
     * @return array<string, string> path inside shelf/ => the snapshot file it comes from
     */
    public static function state(string $root, string $snapshot): array
    {
        $chapter = basename(dirname($snapshot));
        $dirs = glob("$root/code/[0-9][0-9]-*/shelf", GLOB_ONLYDIR) ?: [];
        sort($dirs);
        $files = [];
        foreach ($dirs as $dir) {
            if (strcmp(basename(dirname($dir)), $chapter) > 0) {
                break;
            }
            foreach (Run::files($dir, '') as $file) {
                $path = substr($file, strlen($dir) + 1);
                if (str_ends_with($path, self::REMOVED)) {
                    unset($files[substr($path, 0, -strlen(self::REMOVED))]);
                } else {
                    $files[$path] = $file;
                }
            }
        }
        ksort($files);

        return $files;
    }

    /** Writes the state of Shelf after a snapshot's chapter into $target. */
    public static function assemble(string $root, string $snapshot, string $target): void
    {
        foreach (self::state($root, $snapshot) as $path => $file) {
            if (!is_dir(dirname("$target/$path"))) {
                mkdir(dirname("$target/$path"), 0o777, true);
            }
            copy($file, "$target/$path");
        }
    }
}
