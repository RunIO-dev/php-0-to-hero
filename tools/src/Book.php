<?php

declare(strict_types=1);

namespace Tools;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Tempest\Highlight\CommonMark\HighlightExtension;

/** Builds the HTML book from book/<lang>/*.md (docs/standards.md §1). */
final class Book
{
    public const TITLE = 'PHP 0 to Hero';

    /**
     * Replaces every <!-- include: path --> with the file as a code block, followed
     * by its .out file as the output, so the text always shows the tested code.
     */
    public static function expand(string $root, string $markdown, string $lang = 'en'): string
    {
        return (string) preg_replace_callback(Chapters::INCLUDE, static function (array $m) use ($root, $lang): string {
            $path = $m[1];
            $file = Chapters::source($root, $path)
                ?? throw new \RuntimeException("Include not found in code/: $path");
            $code = rtrim((string) file_get_contents($file));
            $label = str_ends_with($path, '.bad.php') ? '✗ ' . self::word('wrong', $lang) . ': ' : '';
            $block = sprintf("%s`%s`\n\n```%s\n%s\n```\n", $label, $path, pathinfo($path, PATHINFO_EXTENSION), $code);
            $out = substr($file, 0, -4) . '.out';
            if (str_ends_with($path, '.php') && is_file($out)) {
                $block .= sprintf("\n%s:\n\n```txt\n%s\n```\n", self::word('output', $lang), rtrim((string) file_get_contents($out)));
            }

            return $block;
        }, $markdown);
    }

    public static function html(string $markdown): string
    {
        // Chapters are Markdown only: raw HTML and javascript: links are removed.
        $env = new Environment(['html_input' => 'strip', 'allow_unsafe_links' => false]);
        $env->addExtension(new CommonMarkCoreExtension());
        $env->addExtension(new GithubFlavoredMarkdownExtension());
        $env->addExtension(new HighlightExtension());

        return (string) (new MarkdownConverter($env))->convert($markdown);
    }

    public static function page(string $title, string $body, string $lang): string
    {
        $dir = $lang === 'ar' ? 'rtl' : 'ltr';
        $title = htmlspecialchars($title, ENT_QUOTES);

        return <<<HTML
            <!doctype html>
            <html lang="$lang" dir="$dir">
            <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>$title</title>
            <link rel="stylesheet" href="../book.css">
            </head>
            <body>
            <main>
            $body
            </main>
            </body>
            </html>

            HTML;
    }

    /** The few words the build itself writes, per language edition. */
    private static function word(string $key, string $lang): string
    {
        $words = [
            'en' => ['output' => 'Output', 'wrong' => "Don't do this"],
            'de' => ['output' => 'Ausgabe', 'wrong' => 'So nicht'],
            'ar' => ['output' => 'الناتج', 'wrong' => 'لا تفعل هذا'],
        ];

        return $words[$lang][$key] ?? $words['en'][$key];
    }
}
