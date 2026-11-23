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

    /** Lines of each HTML page a short web transcript shows. */
    public const PAGE_LINES = 8;

    /** Lines kept around each change when a file is shown again. */
    public const CONTEXT = 3;

    /** The longest listing (code and output) the short edition prints, about a page and a half; a longer one needs an excerpt. */
    public const MAX_LINES = 80;

    /** Where the full files are, for the listings the short edition cuts. */
    public const REPO = 'https://github.com/RunIO-dev/php-0-to-hero/blob/main/';

    /** Listings up to this many lines (code and output) are kept on one page in print. */
    public const SHORT = 18;

    /**
     * Replaces every <!-- include: path --> with the file as a code block, followed
     * by its .out file as the output, so the text always shows the tested code.
     *
     * Two editions come from the same chapters:
     * - the extended edition ($shown null) shows every file and every answer in full;
     * - the short edition ($shown an array, the same one for every chapter of an edition, in
     *   order) shows what a chapter is about: an excerpt where the include asks for one
     *   (`lines 40-75`, `requests 2-4`), a Shelf file that an earlier chapter already showed as
     *   its changes only (CONTEXT lines around each), and web pages cut to their content
     *   (mainOnly). Every cut is a marker line; the whole file is in the repo.
     *
     * @param array<string, array{int, list<string>}> $shown file → [chapter, lines]
     */
    public static function expand(string $root, string $markdown, string $lang = 'en', ?array &$shown = null): string
    {
        return (string) preg_replace_callback(Chapters::INCLUDE, static function (array $m) use ($root, $lang, &$shown): string {
            $path = $m[1];
            $file = Chapters::source($root, $path)
                ?? throw new \RuntimeException("Include not found in code/ or shelf/: $path");
            $short = $shown !== null;
            $kind = ($m[2] ?? '') === '' ? null : $m[2];
            $ranges = $kind === null ? [] : self::ranges($m[3] ?? '');
            // code/NN-slug/shelf/… is Shelf as it is after chapter NN
            $shelf = preg_match('~^code/(\d+)-[^/]+/(shelf/.+)$~', $path, $s) === 1 ? [(int) $s[1], $s[2]] : null;
            $note = null;
            $show = static function (string $text, string $key, string $ext, ?\Closure $cut) use (&$shown, $short, $shelf, $lang, &$note): string {
                if (!$short) {
                    return $text;
                }
                $lines = explode("\n", $text);
                $before = $shelf === null ? null : ($shown[$key] ?? null);
                if ($shelf !== null) {
                    $shown[$key] = [$shelf[0], $lines];
                }
                if ($cut !== null) {
                    $note = self::word('excerpt', $lang);
                    /** @var list<string> $piece */
                    $piece = $cut($text);

                    return implode("\n", $piece);
                }
                // a file too long to show whole is shown as its changes, however many there are
                $changes = $before === null ? null : self::changes($before[1], $lines, $ext, $lang, count($lines) > self::MAX_LINES);
                if ($changes === null) {
                    return $text;
                }
                $note ??= sprintf(self::word('since', $lang), $before[0]);

                return implode("\n", $changes);
            };
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $codeCut = match ($kind) {
                'lines' => static fn(string $text): array => self::excerpt(explode("\n", $text), $ranges, $ext, $lang),
                'requests' => static fn(string $text): array => self::requests(explode("\n", $text), $ranges, $lang),
                default => null,
            };
            $transcript = substr($file, 0, -5) . '.out';
            if ($short && str_ends_with($path, '.http') && is_file($transcript)) {
                // one block: the transcript, with each request's comment from the .http file above it
                $text = self::annotated(explode("\n", rtrim((string) file_get_contents($file))), explode("\n", rtrim((string) file_get_contents($transcript))));
                $outCut = $kind === 'requests' ? static fn(string $text): array => self::exchanges(explode("\n", $text), $ranges, $lang) : null;
                $text = $show(self::mainOnly($text, $lang), ($shelf[1] ?? '') . '#out', 'txt', $outCut);

                return sprintf("`%s`%s\n\n```txt\n%s\n```\n", $path, $note === null ? '' : " · $note", $text);
            }
            $code = $show(rtrim((string) file_get_contents($file)), $shelf[1] ?? '', $ext, $codeCut);
            $label = match (true) {
                str_ends_with($path, '.bad.php') => '✗ ' . self::word('wrong', $lang) . ': ',
                str_ends_with($path, '.error.php') => '⚠ ' . self::word('error', $lang) . ': ',
                default => '',
            };
            $block = sprintf("```%s\n%s\n```\n", $ext, $code);
            $out = substr($file, 0, -4) . '.out';
            // a page with its own .http file: its .out is that file's transcript, which the .http's listing shows
            $page = str_ends_with($path, '.php') && is_file(substr($file, 0, -4) . '.http');
            if (str_ends_with($path, '.php') && is_file($out) && !($short && $page)) {
                $command = Examples::command($file);
                $typed = $command['custom'] && Examples::session($file) === null ? '$ ' . $command['line'] . "\n" : '';
                $text = $show($typed . rtrim((string) file_get_contents($out)), ($shelf[1] ?? '') . '#out', 'txt', null);
                $block .= sprintf("\n%s:\n\n```txt\n%s\n```\n", self::word('output', $lang), $text);
            }
            if (str_ends_with($path, '.http') && is_file(substr($file, 0, -5) . '.out')) {
                $text = rtrim((string) file_get_contents(substr($file, 0, -5) . '.out'));
                $outCut = $kind === 'requests'
                    ? static fn(string $text): array => self::exchanges(explode("\n", $text), $ranges, $lang)
                    : null;
                $text = $show($short ? self::mainOnly($text, $lang) : $text, ($shelf[1] ?? '') . '#out', 'txt', $outCut);
                $block .= sprintf("\n%s:\n\n```txt\n%s\n```\n", self::word('output', $lang), $text);
            }
            $block = sprintf("%s`%s`%s\n\n", $label, $path, $note === null ? '' : " · $note") . $block;
            $analysis = substr($file, 0, -4) . '.phpstan';
            if (str_ends_with($path, '.php') && is_file($analysis)) {
                $block .= sprintf("\nPHPStan:\n\n```txt\n%s\n```\n", rtrim((string) file_get_contents($analysis)));
            }

            return $block;
        }, $markdown);
    }

    /** The lines of an .http file that tell the checks what to do, not the reader why. */
    private const DIRECTIVE = '~^# (run|env|docroot|router|today|capture) ~';

    /**
     * A web transcript with the comments of its .http file: each request's comment above its
     * exchange, and the file's own comments (before its # run lines) at the top. The transcript
     * already shows every request, so the short edition prints this instead of both files.
     * If the two don't match up, the transcript is returned as it is.
     *
     * @param list<string> $http
     * @param list<string> $transcript
     */
    public static function annotated(array $http, array $transcript): string
    {
        // the comments of each request in the .http file; chunk 0's comments before its last directive belong to the file
        $comments = [[]];
        $header = [];
        foreach ($http as $line) {
            if (trim($line) === '###') {
                $comments[] = [];
            } elseif (preg_match(self::DIRECTIVE, $line) === 1) {
                if (count($comments) === 1) {
                    array_push($header, ...$comments[0]);
                    $comments[0] = [];
                }
            } elseif (str_starts_with($line, '#')) {
                $comments[count($comments) - 1][] = $line;
            }
        }
        $exchanges = [[]];
        foreach ($transcript as $line) {
            if (preg_match('~^> [A-Z]+ \S~', $line) === 1) {
                $exchanges[] = [];
            }
            $exchanges[count($exchanges) - 1][] = $line;
        }
        if (count($exchanges) - 1 !== count($comments)) {
            return implode("\n", $transcript);
        }
        $result = [...$header, ...$exchanges[0]];
        foreach (array_slice($exchanges, 1) as $i => $exchange) {
            array_push($result, ...$comments[$i], ...$exchange);
        }

        return implode("\n", $result);
    }

    /**
     * An include's excerpt, "40-75, 90" or "2-4": the ranges, 1-based and inclusive.
     *
     * @return list<array{int, int}>
     */
    public static function ranges(string $spec): array
    {
        $ranges = [];
        foreach (explode(',', $spec) as $part) {
            if (preg_match('~^\s*(\d+)(?:\s*-\s*(\d+))?\s*$~', $part, $r) !== 1) {
                throw new \RuntimeException("Not a range: \"$part\" (write 40-75, or 40-75, 90)");
            }
            $from = (int) $r[1];
            $to = (int) ($r[2] ?? $r[1]);
            if ($to < $from) {
                throw new \RuntimeException("Range ends before it starts: $part");
            }
            $ranges[] = [$from, $to];
        }

        return $ranges;
    }

    /**
     * The lines of a file in the ranges; each run of other lines is one marker line.
     *
     * @param list<string> $lines
     * @param list<array{int, int}> $ranges 1-based, inclusive
     * @return list<string>
     */
    public static function excerpt(array $lines, array $ranges, string $ext, string $lang = 'en'): array
    {
        $keep = [];
        foreach ($ranges as [$from, $to]) {
            if ($to > count($lines)) {
                throw new \RuntimeException(sprintf('Lines %d-%d: the file has %d lines', $from, $to, count($lines)));
            }
            for ($i = $from; $i <= $to; $i++) {
                $keep[$i - 1] = true;
            }
        }

        return self::keep($lines, $keep, self::marker($ext, 'skipped', $lang));
    }

    /**
     * The requests of an .http file with these numbers (1 is the first); the lines before the
     * first request (the file's comments and # run lines) stay.
     *
     * @param list<string> $lines
     * @param list<array{int, int}> $ranges
     * @return list<string>
     */
    public static function requests(array $lines, array $ranges, string $lang = 'en'): array
    {
        $chunks = [[]];
        foreach ($lines as $line) {
            if (trim($line) === '###') {
                $chunks[] = [];
            } else {
                $chunks[count($chunks) - 1][] = $line;
            }
        }

        return self::pick($chunks, $ranges, '###', '# ' . self::word('requests', $lang), 1, $lang);
    }

    /**
     * The exchanges of a web transcript with these numbers (1 is the first request); 0 is the
     * output of the # run commands before it.
     *
     * @param list<string> $lines
     * @param list<array{int, int}> $ranges
     * @return list<string>
     */
    public static function exchanges(array $lines, array $ranges, string $lang = 'en'): array
    {
        $chunks = [[]];
        $pending = []; // comment lines: they belong to the request after them
        foreach ($lines as $line) {
            if (str_starts_with($line, '# ')) {
                $pending[] = $line;
                continue;
            }
            if (preg_match('~^> [A-Z]+ \S~', $line) === 1) {
                $chunks[] = [];
            }
            array_push($chunks[count($chunks) - 1], ...$pending, ...[$line]);
            $pending = [];
        }
        array_push($chunks[count($chunks) - 1], ...$pending);

        return self::pick($chunks, $ranges, null, self::word('requests', $lang), 0, $lang);
    }

    /**
     * @param list<list<string>> $chunks
     * @param list<array{int, int}> $ranges
     * @param int $first the number of chunk 0: 1 in an .http file (its first request, after the
     *                   file's comments), 0 in a transcript (the output of its # run commands)
     * @return list<string>
     */
    private static function pick(array $chunks, array $ranges, ?string $separator, string $marker, int $first, string $lang): array
    {
        $wanted = [];
        foreach ($ranges as [$from, $to]) {
            for ($i = $from; $i <= $to; $i++) {
                $wanted[$i] = true;
            }
        }
        $highest = count($chunks) - 1 + $first;
        if ($wanted !== [] && max(array_keys($wanted)) > $highest) {
            throw new \RuntimeException(sprintf('Request %d: there are %d requests', max(array_keys($wanted)), $highest));
        }
        $result = [];
        $skipped = 0;
        $kept = false;
        foreach ($chunks as $i => $chunk) {
            $number = $i + $first;
            if (!isset($wanted[$number])) {
                if ($number === 0) {
                    // the # run commands' output: a line marker, as it is not a request
                    if ($chunk !== []) {
                        $result[] = sprintf(self::word('skipped', $lang), count($chunk));
                    }
                    continue;
                }
                if ($first === 1 && $i === 0) {
                    // the file's own comments and # run lines come before its first request: they stay
                    foreach ($chunk as $line) {
                        if (!str_starts_with($line, '#')) {
                            break;
                        }
                        $result[] = $line;
                    }
                }
                $skipped++;
                continue;
            }
            if ($skipped > 0) {
                $result[] = sprintf($marker, $skipped);
                $result[] = '';
            } elseif ($kept && $separator !== null) {
                $result[] = $separator;
            }
            $skipped = 0;
            $kept = true;
            array_push($result, ...$chunk);
        }
        if ($skipped > 0) {
            $result[] = sprintf($marker, $skipped);
        }

        return $result;
    }

    /**
     * @param list<string> $lines
     * @param array<int, true> $keep 0-based
     * @param string $marker with %d for the number of lines left out
     * @return list<string>
     */
    private static function keep(array $lines, array $keep, string $marker): array
    {
        $result = [];
        $skipped = 0;
        $total = count($lines);
        for ($k = 0; $k <= $total; $k++) {
            if ($k < $total && !isset($keep[$k])) {
                $skipped++;
                continue;
            }
            if ($skipped === 1) {
                $result[] = $lines[$k - 1];
            } elseif ($skipped > 1) {
                $result[] = sprintf($marker, $skipped);
            }
            $skipped = 0;
            if ($k < $total) {
                $result[] = $lines[$k];
            }
        }

        return $result;
    }

    /** A marker line in the file's own comment syntax, with %d for the number. */
    private static function marker(string $ext, string $word, string $lang): string
    {
        [$open, $close] = match ($ext) {
            'php', 'js' => ['// ', ''],
            'twig' => ['{# ', ' #}'],
            'html', 'xml' => ['<!-- ', ' -->'],
            'css' => ['/* ', ' */'],
            'sql' => ['-- ', ''],
            'txt', 'out', 'json' => ['', ''],
            default => ['# ', ''],
        };

        return $open . self::word($word, $lang) . $close;
    }

    /**
     * A web transcript with each HTML page cut to the start of its <main> element: the <head>,
     * the site's header and navigation, and the footer are the same on every page, so each
     * becomes one marker line, and the content shows its first PAGE_LINES lines. A page without
     * <main> starts after its </head>. Requests, status lines and headers stay as they are.
     */
    public static function mainOnly(string $transcript, string $lang = 'en'): string
    {
        $lines = explode("\n", $transcript);
        $result = [];
        // a run of one line is shown: a marker would not be shorter
        $omit = static function (array $lines) use (&$result, $lang): void {
            if (count($lines) === 1) {
                $result[] = $lines[0];
            } elseif ($lines !== []) {
                $result[] = sprintf(self::word('omitted', $lang), count($lines));
            }
        };
        $count = count($lines);
        for ($i = 0; $i < $count; $i++) {
            if (preg_match('~^\s*<!doctype html~i', $lines[$i]) !== 1) {
                $result[] = $lines[$i];
                continue;
            }
            // the page runs to its </html>
            $end = $i;
            while ($end < $count - 1 && preg_match('~^\s*</html>~i', $lines[$end]) !== 1) {
                $end++;
            }
            $page = array_slice($lines, $i, $end - $i + 1);
            $from = self::lineOf($page, '~<main\b~i');
            $to = self::lineOf($page, '~</main>~i');
            if ($from === null || $to === null || $to < $from) {
                $head = self::lineOf($page, '~</head>~i');
                $from = $head === null ? 0 : $head + 1;
                $to = count($page) - 1;
            }
            $omit(array_slice($page, 0, $from));
            $shown = min($to - $from + 1, self::PAGE_LINES);
            array_push($result, ...array_slice($page, $from, $shown));
            $omit(array_slice($page, $from + $shown));
            $i = $end;
        }

        return implode("\n", $result);
    }

    /** @param list<string> $lines */
    private static function lineOf(array $lines, string $pattern): ?int
    {
        foreach ($lines as $i => $line) {
            if (preg_match($pattern, $line) === 1) {
                return $i;
            }
        }

        return null;
    }

    /**
     * The new version of a file as its changes since the old one: changed lines with CONTEXT
     * lines around them, each longer run of unchanged lines as one marker line. Null when that
     * would save too little to be worth reading in pieces, unless $always.
     *
     * @param list<string> $old
     * @param list<string> $new
     * @return list<string>|null
     */
    public static function changes(array $old, array $new, string $ext, string $lang = 'en', bool $always = false): ?array
    {
        $total = count($new);
        if ($total < 25 && !$always) {
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
        $result = self::keep($new, $keep, self::marker($ext, 'unchanged', $lang));

        return count($result) > 0.8 * $total && !$always ? null : $result;
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
                // a cut listing names the cut and links to the whole file (the link is left out in print)
                $since = ($m[3] ?? '') === '' ? '' : sprintf(
                    '<span class="since">%s</span><a class="full" href="%s">%s</a>',
                    $m[3],
                    htmlspecialchars(self::REPO . html_entity_decode($m[2], ENT_QUOTES), ENT_QUOTES),
                    self::word('full', $lang),
                );
                $out = ($m[5] ?? '') === '' ? '' : sprintf('<div class="out"><div class="out-h">%s</div>%s</div>', self::word('output', $lang), $m[5]);
                $out .= ($m[6] ?? '') === '' ? '' : sprintf('<div class="out"><div class="out-h">PHPStan</div>%s</div>', $m[6]);
                // a short listing stays on one page in print; a long one may continue on the next
                $short = substr_count($m[4] . $out, "\n") <= self::SHORT ? ' short' : '';

                $lines = substr_count($m[4] . $out, "\n");

                return sprintf('<figure class="listing %s%s" data-lines="' . $lines . '"><figcaption><span class="dots"><i></i><i></i><i></i></span><span class="path">%s</span>%s%s</figcaption>%s%s</figure>', $kind, $short, $m[2], $since, $badge, $m[4], $out);
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
     * The listings of a structured chapter that are longer than MAX_LINES, as "path: N lines".
     *
     * @return list<string>
     */
    public static function tooLong(string $html): array
    {
        preg_match_all('~<figure class="listing[^"]*" data-lines="(\d+)"><figcaption>.*?<span class="path">([^<]+)</span>~', $html, $m, PREG_SET_ORDER);
        $long = [];
        foreach ($m as [, $lines, $path]) {
            if ((int) $lines > self::MAX_LINES) {
                $long[] = sprintf('%s: %d lines', html_entity_decode($path, ENT_QUOTES), $lines);
            }
        }

        return $long;
    }

    /**
     * The printed book's first pages: the cover and the contents.
     *
     * @param list<array{int, string}> $chapters number and title
     * @param array{name: string, homepage: string} $author from composer.json "authors"
     * @param int|null $volume the paperback's volume (VOLUMES), named on the title page
     * @param bool $extended the extended edition (every file in full), named on the title page
     */
    public static function front(string $lang, string $version, array $chapters, array $author, string $date, ?int $volume = null, bool $extended = false): string
    {
        $part = $extended ? ' · ' . self::word('extended', $lang) : '';
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
                'volume' => 'Volume %d · chapters %d–%d', 'omitted' => '⋯ %d lines of the page left out',
                'extended' => 'Extended edition: every file in full', 'full' => 'whole file', 'excerpt' => 'excerpt', 'skipped' => '⋯ %d lines left out', 'requests' => '⋯ requests left out: %d',
                'imprint' => '<p><strong>PHP 0 to Hero</strong>, %4$s, PHP %5$s.<br>© %1$s %2$s · <a href="%3$s">%6$s</a></p>'
                    . '<p>The text of this book is licensed under the Creative Commons Attribution 4.0 International licence (CC BY 4.0): you may share and adapt it for any purpose, also commercially, as long as you credit the author. The code is licensed under the MIT licence.</p>'
                    . '<p>To credit this book, you can write:<br><em>“PHP 0 to Hero” by %2$s, %3$s, licensed under CC BY 4.0.</em></p>'
                    . '<p>The code, the exercises and the latest edition: <a href="https://github.com/RunIO-dev/php-0-to-hero">github.com/RunIO-dev/php-0-to-hero</a>. Corrections are welcome there.</p>'],
            'de' => ['output' => 'Ausgabe', 'wrong' => 'So nicht', 'error' => 'Das zeigt einen Fehler', 'chapter' => 'Kapitel',
                'contents' => 'Inhalt', 'subtitle' => 'Von der ersten Codezeile bis zur sicheren Webanwendung',
                'edition' => 'Deutsche Ausgabe', 'licence' => 'Text CC BY 4.0 · Code MIT', 'by' => 'von',
                'since' => 'Änderungen seit Kapitel %d', 'unchanged' => '⋯ %d unveränderte Zeilen',
                'volume' => 'Band %d · Kapitel %d–%d', 'omitted' => '⋯ %d Zeilen der Seite ausgelassen',
                'extended' => 'Erweiterte Ausgabe: alle Dateien vollständig', 'full' => 'ganze Datei', 'excerpt' => 'Auszug', 'skipped' => '⋯ %d Zeilen ausgelassen', 'requests' => '⋯ Anfragen ausgelassen: %d'],
            'ar' => ['output' => 'الناتج', 'wrong' => 'لا تفعل هذا', 'error' => 'هذا يُظهر خطأً', 'chapter' => 'الفصل',
                'contents' => 'المحتويات', 'subtitle' => 'من أول سطر برمجي إلى تطبيق ويب آمن',
                'edition' => 'الطبعة العربية', 'licence' => 'النص CC BY 4.0 · الشيفرة MIT', 'by' => 'تأليف',
                'since' => 'التغييرات منذ الفصل %d', 'unchanged' => '⋯ أسطر لم تتغير: %d',
                'volume' => 'الجزء %d · الفصول %d–%d', 'omitted' => '⋯ أسطر محذوفة من الصفحة: %d',
                'extended' => 'الطبعة الموسّعة: كل الملفات كاملة', 'full' => 'الملف كاملًا', 'excerpt' => 'مقتطف', 'skipped' => '⋯ أسطر محذوفة: %d', 'requests' => '⋯ طلبات محذوفة: %d'],
        ];

        return $words[$lang][$key] ?? $words['en'][$key];
    }
}
