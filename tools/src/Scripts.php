<?php

declare(strict_types=1);

namespace Tools;

/**
 * A file that starts with "#!" is run as a program (Shelf's docker/entrypoint.sh, deploy/deploy.sh), so
 * git must store it as executable (mode 100755). On a disk that shows every file as executable, or with
 * core.fileMode off, git stores 100644 without a word, and the file fails only in a fresh checkout:
 * Shelf's production container did, in CI (chapter 78).
 */
final class Scripts
{
    /** @return list<string> */
    public static function check(string $root): array
    {
        $run = Run::capture(['git', '-c', 'safe.directory=*', 'ls-files', '--stage', '-z'], $root);
        if ($run['code'] !== 0) {
            return ['git ls-files failed: ' . trim($run['err'])];
        }
        $problems = [];
        foreach (array_filter(explode("\0", $run['out'])) as $entry) {
            [$meta, $path] = explode("\t", $entry, 2);
            if (str_starts_with($meta, '100644 ') && self::isScript("$root/$path")) {
                $problems[] = "$path: starts with #! but is not executable in git; run: git update-index --chmod=+x $path";
            }
        }

        return $problems;
    }

    private static function isScript(string $file): bool
    {
        $handle = @fopen($file, 'r');
        if ($handle === false) {
            return false; // deleted, but not yet staged
        }
        $start = fread($handle, 2);
        fclose($handle);

        return $start === '#!';
    }
}
