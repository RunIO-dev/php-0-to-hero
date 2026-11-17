<?php

declare(strict_types=1);

namespace Tools;

/**
 * From chapter 36, Shelf is a Composer project of its own: a folder with a
 * composer.json whose classes, in a folder such as src/, Composer's autoloader
 * loads. Only the scripts next to composer.json run; the classes are checked
 * through them. Composer writes the autoloader into vendor/, which git ignores,
 * so the checks write it before they run anything (docs/standards.md §4).
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

    /** True for a file that the autoloader loads: inside a project, below the folder of its composer.json. */
    public static function isAutoloaded(string $file): bool
    {
        $project = self::of($file);

        return $project !== null && dirname($file) !== $project;
    }

    /**
     * Writes the autoloader of every project in code/ and shelf/.
     *
     * @return list<string> problems
     */
    public static function prepare(string $root): array
    {
        $problems = [];
        $composer = getenv('COMPOSER_BINARY');
        $command = is_string($composer) && $composer !== '' ? [PHP_BINARY, $composer] : ['composer'];
        foreach ([...Run::files("$root/code", '/composer.json'), ...Run::files("$root/shelf", '/composer.json')] as $file) {
            $run = Run::capture([...$command, 'dump-autoload', '--quiet', '--no-interaction'], dirname($file));
            if ($run['code'] !== 0) {
                $problems[] = substr(dirname($file), strlen($root) + 1) . ': composer dump-autoload failed: ' . trim($run['out'] . $run['err']);
            }
        }

        return $problems;
    }
}
