<?php

declare(strict_types=1);

namespace Tools;

/**
 * MariaDB, the db service of compose.yaml (chapter 59). Examples and exercises that connect to it
 * (their code says host=db) need it running; when it isn't, the checks say so once and skip them,
 * instead of reporting a network error for each file.
 */
final class Database
{
    public const string START = 'start the database first: docker compose up -d --wait db';

    private static ?bool $up = null;

    /**
     * True for a file that connects to the db service, itself or through a file it loads with
     * require_once __DIR__ . '/name.php' (db.php in chapter 59). For a folder: any of its PHP files.
     */
    public static function isNeeded(string $path): bool
    {
        $files = is_dir($path) ? Run::files($path, '.php') : [$path];
        foreach ($files as $file) {
            $code = (string) file_get_contents($file);
            preg_match_all('~\b(?:require|include)(?:_once)?\s*\(?\s*__DIR__\s*\.\s*[\'"](/[\w./-]+\.php)[\'"]~', $code, $m);
            foreach ($m[1] as $loaded) {
                $code .= is_file(dirname($file) . $loaded) ? (string) file_get_contents(dirname($file) . $loaded) : '';
            }
            if (str_contains($code, 'host=db')) {
                return true;
            }
        }

        return false;
    }

    /** Docker's network knows the name db only while the db container runs. */
    public static function isUp(): bool
    {
        return self::$up ??= gethostbyname('db') !== 'db';
    }
}
