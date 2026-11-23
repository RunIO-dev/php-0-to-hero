<?php

declare(strict_types=1);

namespace Tools;

/** Every chapter has the template headings in order, and every include exists (docs/standards.md §2). */
final class Chapters
{
    /** Files of the course itself that a chapter may include, besides code/ and shelf/. */
    public const array COURSE_FILES = ['compose.yaml', 'Dockerfile', 'docker/db/users.sh'];

    /** The template headings per language edition. A new edition adds its list here. */
    public const HEADINGS = [
        'en' => ['Goals', 'Before you start', 'The idea', 'In code', 'Common mistakes', 'Principle check',
            'Security', 'Shelf', 'Exercises', 'Summary', 'Further reading'],
    ];

    /** <!-- include: path -->, or with an excerpt: <!-- include: path lines 40-75 -->, <!-- include: x.http requests 2-4 --> */
    public const INCLUDE = '/<!--\s*include:\s*(\S+)(?:\s+(lines|requests)\s+([0-9][0-9,\s-]*?))?\s*-->/';

    /** @return list<string> */
    public static function check(string $root): array
    {
        $problems = [];
        foreach (glob($root . '/book/*', GLOB_ONLYDIR) ?: [] as $dir) {
            $lang = basename($dir);
            foreach (glob($dir . '/*.md') ?: [] as $file) {
                $name = "book/$lang/" . basename($file);
                if (preg_match('/^\d{2}-[a-z0-9-]+\.md$/', basename($file)) !== 1) {
                    $problems[] = "$name: name must be NN-slug.md";
                }
                $text = (string) file_get_contents($file);
                array_push($problems, ...self::headings($name, $text, self::HEADINGS[$lang] ?? null));
                array_push($problems, ...self::includes($root, $name, $text));
            }
        }

        return $problems;
    }

    /**
     * @param list<string>|null $want
     * @return list<string>
     */
    public static function headings(string $name, string $text, ?array $want): array
    {
        if ($want === null) {
            return ["$name: no heading list for this language in Tools\\Chapters::HEADINGS"];
        }
        preg_match_all('/^## (.+?)\s*$/m', $text, $m);
        if ($m[1] !== $want) {
            $missing = array_values(array_diff($want, $m[1]));
            $why = $missing === [] ? 'in the wrong order or extra' : 'missing: ' . implode(', ', $missing);

            return ["$name: the ## headings must be exactly the template ($why)"];
        }

        return [];
    }

    /** The real path of an included file, or null unless it is a file inside code/ or shelf/. */
    public static function source(string $root, string $path): ?string
    {
        $file = realpath($root . '/' . $path);
        if ($file === false || !is_file($file)) {
            return null;
        }
        if (in_array($path, self::COURSE_FILES, true)) {
            return $file; // the course's own setup, which chapter 45 explains
        }
        foreach (['code', 'shelf'] as $top) {
            $dir = realpath($root . '/' . $top);
            if ($dir !== false && str_starts_with($file, $dir . '/')) {
                return $file;
            }
        }

        return null;
    }

    /** @return list<string> */
    public static function includes(string $root, string $name, string $text): array
    {
        $problems = [];
        preg_match_all(self::INCLUDE, $text, $m);
        foreach ($m[1] as $path) {
            if (self::source($root, $path) === null) {
                $problems[] = "$name: include not found in code/ or shelf/: $path";
            }
        }

        return $problems;
    }
}
