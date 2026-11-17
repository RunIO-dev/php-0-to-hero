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
                ?? throw new \RuntimeException("Include not found in code/ or shelf/: $path");
            $code = rtrim((string) file_get_contents($file));
            $label = match (true) {
                str_ends_with($path, '.bad.php') => '✗ ' . self::word('wrong', $lang) . ': ',
                str_ends_with($path, '.error.php') => '⚠ ' . self::word('error', $lang) . ': ',
                default => '',
            };
            $block = sprintf("%s`%s`\n\n```%s\n%s\n```\n", $label, $path, pathinfo($path, PATHINFO_EXTENSION), $code);
            $out = substr($file, 0, -4) . '.out';
            if (str_ends_with($path, '.php') && is_file($out)) {
                $command = Examples::command($file);
                $typed = $command['custom'] ? '$ ' . $command['line'] . "\n" : '';
                $block .= sprintf("\n%s:\n\n```txt\n%s%s\n```\n", self::word('output', $lang), $typed, rtrim((string) file_get_contents($out)));
            }
            if (str_ends_with($path, '.http') && is_file(substr($file, 0, -5) . '.out')) {
                $block .= sprintf("\n%s:\n\n```txt\n%s\n```\n", self::word('output', $lang), rtrim((string) file_get_contents(substr($file, 0, -5) . '.out')));
            }
            $analysis = substr($file, 0, -4) . '.phpstan';
            if (str_ends_with($path, '.php') && is_file($analysis)) {
                $block .= sprintf("\nPHPStan:\n\n```txt\n%s\n```\n", rtrim((string) file_get_contents($analysis)));
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

    /**
     * Gives the converted chapter the structure the book's design needs: an opening with the
     * chapter number, one <section> per template heading (class s-goals, s-summary, ...), and
     * each included file as a listing with its file name and its output.
     */
    public static function structure(string $html, string $lang): string
    {
        // the chapter opening: "Chapter 6" above the title
        $html = (string) preg_replace_callback('~<h1>(\d+)\.\s*(.+?)</h1>~', static fn(array $m): string => sprintf(
            '<header class="opener"><div class="opener-num">%2$d</div><div class="opener-t"><div class="opener-k">%1$s</div><h1>%3$s</h1></div></header>',
            self::word('chapter', $lang),
            (int) $m[1],
            $m[2],
        ), $html, 1);

        $output = preg_quote(self::word('output', $lang), '~');
        $html = (string) preg_replace_callback(
            '~<p>(?:(✗|⚠) [^<]*: )?<code>([^<]+)</code></p>\s*(<pre data-lang="[a-z]+".*?</pre>)(?:\s*<p>' . $output . ':</p>\s*(<pre data-lang="txt".*?</pre>))?(?:\s*<p>PHPStan:</p>\s*(<pre data-lang="txt".*?</pre>))?~s',
            static function (array $m) use ($lang): string {
                $kind = ['✗' => 'bad', '⚠' => 'error'][$m[1]] ?? 'ok';
                $badge = $kind === 'ok' ? '' : sprintf('<span class="badge">%s %s</span>', $m[1], self::word($kind === 'bad' ? 'wrong' : 'error', $lang));
                $out = ($m[4] ?? '') === '' ? '' : sprintf('<div class="out"><div class="out-h">%s</div>%s</div>', self::word('output', $lang), $m[4]);
                $out .= ($m[5] ?? '') === '' ? '' : sprintf('<div class="out"><div class="out-h">PHPStan</div>%s</div>', $m[5]);

                return sprintf('<figure class="listing %s"><figcaption><span class="dots"><i></i><i></i><i></i></span><span class="path">%s</span>%s</figcaption>%s%s</figure>', $kind, $m[2], $badge, $m[3], $out);
            },
            $html,
        );

        // bold text ending in a colon is a label ("Fix:"), not a key term: no highlighter on it
        $html = (string) preg_replace('~<strong>((?:(?!</strong>).)*:)</strong>~', '<strong class="label">$1</strong>', $html);

        // one section per template heading, named after its place in the English template
        $parts = preg_split('~(?=<h2>)~', $html) ?: [$html];
        $out = array_shift($parts);
        foreach ($parts as $i => $part) {
            $name = Chapters::HEADINGS['en'][$i] ?? 'extra';
            $out .= sprintf('<section class="s-%s">%s</section>', strtolower(str_replace(' ', '-', $name)), $part);
        }

        return $out;
    }

    /**
     * The printed book's first pages: the cover and the contents.
     *
     * @param list<array{int, string}> $chapters number and title
     * @param array{name: string, homepage: string} $author from composer.json "authors"
     */
    public static function front(string $lang, string $version, array $chapters, array $author, string $date): string
    {
        $name = htmlspecialchars($author['name'], ENT_QUOTES);
        $home = htmlspecialchars($author['homepage'], ENT_QUOTES);
        $site = (string) preg_replace('~^https?://~', '', $home);
        $items = '';
        foreach ($chapters as [$number, $title]) {
            $items .= sprintf('<li><span class="toc-num">%02d</span><span class="toc-title">%s</span></li>', $number, htmlspecialchars($title, ENT_QUOTES));
        }

        $imprint = sprintf(
            self::word('imprint', $lang),
            substr($date, 0, 4),
            $name,
            $home,
            self::word('edition', $lang),
            htmlspecialchars($version, ENT_QUOTES),
            $site . ' · ' . htmlspecialchars($date, ENT_QUOTES),
        );

        return '<section class="cover"><div class="cover-code">&lt;?php</div><h1>PHP<br>0 to Hero</h1>'
            . '<p class="cover-sub">' . self::word('subtitle', $lang) . '</p>'
            . '<p class="cover-meta">' . self::word('edition', $lang) . ' · PHP ' . htmlspecialchars($version, ENT_QUOTES) . '</p>'
            . '<p class="cover-author"><span>' . self::word('by', $lang) . '</span> ' . $name . '</p>'
            . '<p class="cover-foot">' . $site . ' · RunIO-dev/php-0-to-hero · ' . self::word('licence', $lang) . '</p></section>'
            . '<section class="imprint">' . $imprint . '</section>'
            . '<section class="toc"><h2>' . self::word('contents', $lang) . '</h2><ol>' . $items . '</ol></section>';
    }

    public static function page(string $title, string $body, string $lang, string $class = 'web'): string
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
            <body class="$class">
            <main>
            $body
            </main>
            </body>
            </html>

            HTML;
    }

    /** The few words the build itself writes, per language edition. */
    public static function word(string $key, string $lang): string
    {
        $words = [
            'en' => ['output' => 'Output', 'wrong' => "Don't do this", 'error' => 'This shows an error', 'chapter' => 'Chapter',
                'contents' => 'Contents', 'subtitle' => 'From your first line of code to a secure web application',
                'edition' => 'English edition', 'licence' => 'Text CC BY 4.0 · Code MIT', 'by' => 'by',
                'imprint' => '<p><strong>PHP 0 to Hero</strong>, %4$s, PHP %5$s.<br>© %1$s %2$s · <a href="%3$s">%6$s</a></p>'
                    . '<p>The text of this book is licensed under the Creative Commons Attribution 4.0 International licence (CC BY 4.0): you may share and adapt it for any purpose, also commercially, as long as you credit the author. The code is licensed under the MIT licence.</p>'
                    . '<p>To credit this book, you can write:<br><em>“PHP 0 to Hero” by %2$s, %3$s, licensed under CC BY 4.0.</em></p>'
                    . '<p>The code, the exercises and the latest edition: <a href="https://github.com/RunIO-dev/php-0-to-hero">github.com/RunIO-dev/php-0-to-hero</a>. Corrections are welcome there.</p>'],
            'de' => ['output' => 'Ausgabe', 'wrong' => 'So nicht', 'error' => 'Das zeigt einen Fehler', 'chapter' => 'Kapitel',
                'contents' => 'Inhalt', 'subtitle' => 'Von der ersten Codezeile bis zur sicheren Webanwendung',
                'edition' => 'Deutsche Ausgabe', 'licence' => 'Text CC BY 4.0 · Code MIT', 'by' => 'von'],
            'ar' => ['output' => 'الناتج', 'wrong' => 'لا تفعل هذا', 'error' => 'هذا يُظهر خطأً', 'chapter' => 'الفصل',
                'contents' => 'المحتويات', 'subtitle' => 'من أول سطر برمجي إلى تطبيق ويب آمن',
                'edition' => 'الطبعة العربية', 'licence' => 'النص CC BY 4.0 · الشيفرة MIT', 'by' => 'تأليف'],
        ];

        return $words[$lang][$key] ?? $words['en'][$key];
    }
}
