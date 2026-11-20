<?php

declare(strict_types=1);

namespace Tools;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\DiffOnlyOutputBuilder;
use Tempest\Highlight\CommonMark\HighlightExtension;

/** Builds the HTML book from book/<lang>/*.md (docs/standards.md §1). */
final class Book
{
    public const TITLE = 'PHP 0 to Hero';

    /** The paperback's volumes, by chapter: Parts 1-8 and Parts 9-14 (KDP prints at most 828 pages). */
    public const VOLUMES = [1 => [1, 60], 2 => [61, 89]];

    /** Lines kept around each change when a file is shown again. */
    public const CONTEXT = 3;

    /** Listings up to this many lines (code and output) are kept on one page in print. */
    public const SHORT = 18;

    /**
     * Replaces every <!-- include: path --> with the file as a code block, followed
     * by its .out file as the output, so the text always shows the tested code.
     *
     * A Shelf file that an earlier chapter of this edition already showed is shown with
     * its changes only: the changed lines, CONTEXT lines around them, and a marker for each
     * run of unchanged lines (the whole file is in the repo). $shown remembers what each
     * chapter showed; pass the same array for every chapter of an edition, in order.
     *
     * @param array<string, array{int, list<string>}> $shown file → [chapter, lines]
     */
    public static function expand(string $root, string $markdown, string $lang = 'en', ?array &$shown = null): string
    {
        return (string) preg_replace_callback(Chapters::INCLUDE, static function (array $m) use ($root, $lang, &$shown): string {
            $path = $m[1];
            $file = Chapters::source($root, $path)
                ?? throw new \RuntimeException("Include not found in code/ or shelf/: $path");
            // code/NN-slug/shelf/… is Shelf as it is after chapter NN
            $shelf = preg_match('~^code/(\d+)-[^/]+/(shelf/.+)$~', $path, $s) === 1 ? [(int) $s[1], $s[2]] : null;
            $since = null;
            $show = static function (string $text, string $key, string $ext) use (&$shown, $shelf, $lang, &$since): string {
                if ($shown === null || $shelf === null) {
                    return $text;
                }
                $lines = explode("\n", $text);
                $before = $shown[$key] ?? null;
                $shown[$key] = [$shelf[0], $lines];
                $changes = $before === null ? null : self::changes($before[1], $lines, $ext, $lang);
                if ($changes === null) {
                    return $text;
                }
                $since = $before[0];

                return implode("\n", $changes);
            };
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $code = $show(rtrim((string) file_get_contents($file)), $shelf[1] ?? '', $ext);
            $label = match (true) {
                str_ends_with($path, '.bad.php') => '✗ ' . self::word('wrong', $lang) . ': ',
                str_ends_with($path, '.error.php') => '⚠ ' . self::word('error', $lang) . ': ',
                default => '',
            };
            $block = sprintf("```%s\n%s\n```\n", $ext, $code);
            $out = substr($file, 0, -4) . '.out';
            if (str_ends_with($path, '.php') && is_file($out)) {
                $command = Examples::command($file);
                $typed = $command['custom'] && Examples::session($file) === null ? '$ ' . $command['line'] . "\n" : '';
                $text = $show($typed . rtrim((string) file_get_contents($out)), ($shelf[1] ?? '') . '#out', 'txt');
                $block .= sprintf("\n%s:\n\n```txt\n%s\n```\n", self::word('output', $lang), $text);
            }
            if (str_ends_with($path, '.http') && is_file(substr($file, 0, -5) . '.out')) {
                $text = $show(rtrim((string) file_get_contents(substr($file, 0, -5) . '.out')), ($shelf[1] ?? '') . '#out', 'txt');
                $block .= sprintf("\n%s:\n\n```txt\n%s\n```\n", self::word('output', $lang), $text);
            }
            $note = $since === null ? '' : ' · ' . sprintf(self::word('since', $lang), $since);
            $block = sprintf("%s`%s`%s\n\n", $label, $path, $note) . $block;
            $analysis = substr($file, 0, -4) . '.phpstan';
            if (str_ends_with($path, '.php') && is_file($analysis)) {
                $block .= sprintf("\nPHPStan:\n\n```txt\n%s\n```\n", rtrim((string) file_get_contents($analysis)));
            }

            return $block;
        }, $markdown);
    }

    /**
     * The new version of a file as its changes since the old one: changed lines with CONTEXT
     * lines around them, each longer run of unchanged lines as one marker line. Null when that
     * would save too little to be worth reading in pieces.
     *
     * @param list<string> $old
     * @param list<string> $new
     * @return list<string>|null
     */
    public static function changes(array $old, array $new, string $ext, string $lang = 'en'): ?array
    {
        $total = count($new);
        if ($total < 25) {
            return null;
        }
        // a line of $new is changed when it was added; a removal marks the line after it
        $changed = [];
        $i = 0;
        foreach ((new Differ(new DiffOnlyOutputBuilder()))->diffToArray($old, $new) as [, $type]) {
            match ($type) {
                Differ::ADDED => $changed[$i++] = true,
                Differ::OLD => $i++,
                Differ::REMOVED => $changed[min($i, $total - 1)] = true,
                default => null,
            };
        }
        $keep = [];
        foreach (array_keys($changed) as $line) {
            for ($k = max(0, $line - self::CONTEXT); $k <= min($total - 1, $line + self::CONTEXT); $k++) {
                $keep[$k] = true;
            }
        }
        [$open, $close] = match ($ext) {
            'php', 'js' => ['// ', ''],
            'twig' => ['{# ', ' #}'],
            'html', 'xml' => ['<!-- ', ' -->'],
            'css' => ['/* ', ' */'],
            'sql' => ['-- ', ''],
            'txt', 'out', 'json' => ['', ''],
            default => ['# ', ''],
        };
        $result = [];
        $skipped = 0;
        for ($k = 0; $k <= $total; $k++) {
            if ($k < $total && !isset($keep[$k])) {
                $skipped++;
                continue;
            }
            if ($skipped === 1) {
                $result[] = $new[$k - 1];
            } elseif ($skipped > 1) {
                $result[] = $open . sprintf(self::word('unchanged', $lang), $skipped) . $close;
            }
            $skipped = 0;
            if ($k < $total) {
                $result[] = $new[$k];
            }
        }

        return count($result) > 0.8 * $total ? null : $result;
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
            '~<p>(?:(✗|⚠) [^<]*: )?<code>([^<]+)</code>(?: · ([^<]+))?</p>\s*(<pre data-lang="[a-z]+".*?</pre>)(?:\s*<p>' . $output . ':</p>\s*(<pre data-lang="txt".*?</pre>))?(?:\s*<p>PHPStan:</p>\s*(<pre data-lang="txt".*?</pre>))?~s',
            static function (array $m) use ($lang): string {
                $kind = ['✗' => 'bad', '⚠' => 'error'][$m[1]] ?? 'ok';
                $badge = $kind === 'ok' ? '' : sprintf('<span class="badge">%s %s</span>', $m[1], self::word($kind === 'bad' ? 'wrong' : 'error', $lang));
                $since = ($m[3] ?? '') === '' ? '' : sprintf('<span class="since">%s</span>', $m[3]);
                $out = ($m[5] ?? '') === '' ? '' : sprintf('<div class="out"><div class="out-h">%s</div>%s</div>', self::word('output', $lang), $m[5]);
                $out .= ($m[6] ?? '') === '' ? '' : sprintf('<div class="out"><div class="out-h">PHPStan</div>%s</div>', $m[6]);
                // a short listing stays on one page in print; a long one may continue on the next
                $short = substr_count($m[4] . $out, "\n") <= self::SHORT ? ' short' : '';

                return sprintf('<figure class="listing %s%s"><figcaption><span class="dots"><i></i><i></i><i></i></span><span class="path">%s</span>%s%s</figcaption>%s%s</figure>', $kind, $short, $m[2], $since, $badge, $m[4], $out);
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
     * @param int|null $volume the paperback's volume (VOLUMES), named on the title page
     */
    public static function front(string $lang, string $version, array $chapters, array $author, string $date, ?int $volume = null): string
    {
        $part = '';
        if ($volume !== null && $chapters !== []) {
            $part = ' · ' . sprintf(self::word('volume', $lang), $volume, $chapters[0][0], $chapters[array_key_last($chapters)][0]);
        }
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
            . '<p class="cover-meta">' . self::word('edition', $lang) . ' · PHP ' . htmlspecialchars($version, ENT_QUOTES) . $part . '</p>'
            . '<p class="cover-author"><span>' . self::word('by', $lang) . '</span> ' . $name . '</p>'
            . '<p class="cover-foot">' . $site . ' · RunIO-dev/php-0-to-hero · ' . self::word('licence', $lang) . '</p></section>'
            . '<section class="imprint">' . $imprint . '</section>'
            . '<section class="toc"><h2>' . self::word('contents', $lang) . '</h2><ol>' . $items . '</ol></section>';
    }

    /** @param list<string> $css stylesheets, relative to the page */
    public static function page(string $title, string $body, string $lang, string $class = 'web', array $css = ['../book.css']): string
    {
        $dir = $lang === 'ar' ? 'rtl' : 'ltr';
        $title = htmlspecialchars($title, ENT_QUOTES);
        $links = implode("\n", array_map(static fn(string $href): string => sprintf('<link rel="stylesheet" href="%s">', htmlspecialchars($href, ENT_QUOTES)), $css));

        return <<<HTML
            <!doctype html>
            <html lang="$lang" dir="$dir">
            <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>$title</title>
            $links
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
                'since' => 'changes since chapter %d', 'unchanged' => '⋯ %d unchanged lines',
                'volume' => 'Volume %d · chapters %d–%d',
                'imprint' => '<p><strong>PHP 0 to Hero</strong>, %4$s, PHP %5$s.<br>© %1$s %2$s · <a href="%3$s">%6$s</a></p>'
                    . '<p>The text of this book is licensed under the Creative Commons Attribution 4.0 International licence (CC BY 4.0): you may share and adapt it for any purpose, also commercially, as long as you credit the author. The code is licensed under the MIT licence.</p>'
                    . '<p>To credit this book, you can write:<br><em>“PHP 0 to Hero” by %2$s, %3$s, licensed under CC BY 4.0.</em></p>'
                    . '<p>The code, the exercises and the latest edition: <a href="https://github.com/RunIO-dev/php-0-to-hero">github.com/RunIO-dev/php-0-to-hero</a>. Corrections are welcome there.</p>'],
            'de' => ['output' => 'Ausgabe', 'wrong' => 'So nicht', 'error' => 'Das zeigt einen Fehler', 'chapter' => 'Kapitel',
                'contents' => 'Inhalt', 'subtitle' => 'Von der ersten Codezeile bis zur sicheren Webanwendung',
                'edition' => 'Deutsche Ausgabe', 'licence' => 'Text CC BY 4.0 · Code MIT', 'by' => 'von',
                'since' => 'Änderungen seit Kapitel %d', 'unchanged' => '⋯ %d unveränderte Zeilen',
                'volume' => 'Band %d · Kapitel %d–%d'],
            'ar' => ['output' => 'الناتج', 'wrong' => 'لا تفعل هذا', 'error' => 'هذا يُظهر خطأً', 'chapter' => 'الفصل',
                'contents' => 'المحتويات', 'subtitle' => 'من أول سطر برمجي إلى تطبيق ويب آمن',
                'edition' => 'الطبعة العربية', 'licence' => 'النص CC BY 4.0 · الشيفرة MIT', 'by' => 'تأليف',
                'since' => 'التغييرات منذ الفصل %d', 'unchanged' => '⋯ أسطر لم تتغير: %d',
                'volume' => 'الجزء %d · الفصول %d–%d'],
        ];

        return $words[$lang][$key] ?? $words['en'][$key];
    }
}
