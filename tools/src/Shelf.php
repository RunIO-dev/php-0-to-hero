<?php

declare(strict_types=1);

namespace Tools;

/**
 * Shelf grows chapter by chapter. A chapter that changes it keeps its version in
 * code/NN-slug/shelf/, and the book includes that snapshot, so an early chapter
 * never shows code from a later one. shelf/ is the newest state: every file in it
 * must equal its newest snapshot (docs/standards.md §1).
 */
final class Shelf
{
    /** @return list<string> */
    public static function check(string $root): array
    {
        $problems = [];
        foreach (Run::files($root . '/shelf', '') as $file) {
            $path = substr($file, strlen($root . '/shelf/'));
            if ($path === 'README.md') {
                continue;
            }
            $snapshots = glob("$root/code/[0-9][0-9]-*/shelf/$path") ?: [];
            if ($snapshots === []) {
                $problems[] = "shelf/$path: no snapshot in code/NN-slug/shelf/ (copy it into the chapter that changes it)";
                continue;
            }
            sort($snapshots);
            $newest = (string) end($snapshots);
            if (file_get_contents($newest) !== file_get_contents($file)) {
                $problems[] = "shelf/$path differs from its newest snapshot " . substr($newest, strlen($root) + 1);
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
}
