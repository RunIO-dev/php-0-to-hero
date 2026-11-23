<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tools\Book;
use Tools\Run;

final class BookTest extends TestCase
{
    private string $root;

    /** @var list<string> folders a test made below code/ */
    private array $extra = [];

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/p0h-book-' . bin2hex(random_bytes(4));
        mkdir($this->root . '/code/06-variables', 0777, true);
        file_put_contents($this->root . '/code/06-variables/name.php', "<?php\n\$name = 'Kenji';\necho \$name;\n");
        file_put_contents($this->root . '/code/06-variables/name.out', 'Kenji');
        file_put_contents($this->root . '/code/06-variables/name.bad.php', "<?php\n\$n = 'Kenji';\n");
    }

    protected function tearDown(): void
    {
        array_map(Run::remove(...), $this->extra);
        array_map('unlink', glob($this->root . '/code/06-variables/*') ?: []);
        rmdir($this->root . '/code/06-variables');
        rmdir($this->root . '/code');
        rmdir($this->root);
    }

    public function testIncludeBecomesCodeAndOutput(): void
    {
        $md = Book::expand($this->root, "Before\n\n<!-- include: code/06-variables/name.php -->\n\nAfter");
        self::assertSame(
            "Before\n\n`code/06-variables/name.php`\n\n```php\n<?php\n\$name = 'Kenji';\necho \$name;\n```\n\nOutput:\n\n```txt\nKenji\n```\n\n\nAfter",
            $md,
        );
    }

    public function testBadExampleIsMarkedInTheEditionsLanguage(): void
    {
        $md = Book::expand($this->root, '<!-- include: code/06-variables/name.bad.php -->', 'ar');
        self::assertStringStartsWith('✗ لا تفعل هذا: `code/06-variables/name.bad.php`', $md);
    }

    public function testHtmlIsHighlightedAndEscaped(): void
    {
        $html = Book::html("```php\n<?php echo '<b>';\n```\n");
        self::assertStringContainsString('hl-keyword', $html);
        self::assertStringNotContainsString("'<b>'", $html);
    }

    public function testIncludeOutsideCodeIsRefused(): void
    {
        $this->expectException(\RuntimeException::class);
        Book::expand($this->root, '<!-- include: code/../../../../etc/hostname -->');
    }

    public function testRawHtmlAndScriptLinksAreRemoved(): void
    {
        $html = Book::html("<script>alert(1)</script>\n\n[x](javascript:alert(1))");
        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString('javascript:', $html);
    }

    public function testStructureGivesOpenerListingsAndSections(): void
    {
        $md = "# 6. Variables\n\n## Goals\n\n- one\n\n## Before you start\n\n<!-- include: code/06-variables/name.php -->\n\n<!-- include: code/06-variables/name.bad.php -->\n";
        $html = Book::structure(Book::html(Book::expand($this->root, $md)), 'en');
        self::assertStringContainsString('<header class="opener"><div class="opener-num">6</div><div class="opener-t"><div class="opener-k">Chapter</div><h1>Variables</h1></div></header>', $html);
        self::assertStringContainsString('<section class="s-goals"><h2>Goals</h2>', $html);
        self::assertStringContainsString('<section class="s-before-you-start">', $html);
        self::assertStringContainsString('<figure class="listing ok short" data-lines="4"><figcaption><span class="dots"><i></i><i></i><i></i></span><span class="path">code/06-variables/name.php</span></figcaption><pre', $html);
        self::assertStringContainsString('<div class="out"><div class="out-h">Output</div><pre data-lang="txt"', $html);
        self::assertStringContainsString('<figure class="listing bad short" data-lines="2">', $html);
        self::assertStringContainsString('<span class="badge">✗ Don\'t do this</span>', $html);
    }

    public function testBoldLabelsAreNotHighlightedLikeKeyTerms(): void
    {
        $html = Book::structure(Book::html("A **variable** is a name.\n\n**Fix:** write it.\n"), 'en');
        self::assertStringContainsString('A <strong>variable</strong> is', $html);
        self::assertStringContainsString('<strong class="label">Fix:</strong>', $html);
    }

    public function testArabicPageIsRightToLeft(): void
    {
        self::assertStringContainsString('<html lang="ar" dir="rtl">', Book::page('عنوان', '', 'ar'));
    }

    public function testAShelfFileShownAgainShowsOnlyItsChanges(): void
    {
        $lines = array_map(static fn(int $i): string => "\$line$i = $i;", range(1, 40));
        $this->shelfFile('41-first-tests', "<?php\n" . implode("\n", $lines));
        $lines[19] = '$line20 = 2000;';
        $this->shelfFile('42-testing-well', "<?php\n" . implode("\n", $lines));

        $shown = [];
        $first = Book::expand($this->root, '<!-- include: code/41-first-tests/shelf/src/Loan.php -->', 'en', $shown);
        $again = Book::expand($this->root, '<!-- include: code/42-testing-well/shelf/src/Loan.php -->', 'en', $shown);

        self::assertStringContainsString('$line1 = 1;', $first, 'the first time, the whole file');
        self::assertStringStartsWith('`code/42-testing-well/shelf/src/Loan.php` · changes since chapter 41', $again);
        self::assertStringContainsString("// ⋯ 17 unchanged lines\n\$line17 = 17;", $again);
        self::assertStringContainsString("\$line20 = 2000;", $again);
        self::assertStringContainsString("\$line23 = 23;\n// ⋯ 17 unchanged lines\n```", $again);
        self::assertStringNotContainsString('$line1 = 1;', $again);
    }

    public function testWithoutAMemoryEveryFileIsShownWhole(): void
    {
        $this->shelfFile('41-first-tests', "<?php\n" . str_repeat("echo 1;\n", 40));
        self::assertStringNotContainsString('changes since', Book::expand($this->root, '<!-- include: code/41-first-tests/shelf/src/Loan.php -->'));
    }

    public function testShortFilesAndBigChangesAreShownWhole(): void
    {
        $short = array_map('strval', range(1, 20));
        self::assertNull(Book::changes($short, array_reverse($short), 'php'), 'under 25 lines');
        $long = array_map('strval', range(1, 40));
        $changed = array_map(static fn(string $l): string => $l . '!', $long);
        self::assertNull(Book::changes($long, $changed, 'php'), 'more than 80% changed');
    }

    public function testAnUnchangedFileBecomesOneLineAndOneLineGapsStay(): void
    {
        $lines = array_map('strval', range(1, 30));
        self::assertSame(['# ⋯ 30 unchanged lines'], Book::changes($lines, $lines, 'yaml'));

        $new = $lines;
        $new[3] = 'four';
        $new[11] = 'twelve'; // the contexts end at line 7 and start at line 9: line 8 is shown, not a marker
        self::assertSame(
            ['1', '2', '3', 'four', '5', '6', '7', '8', '9', '10', '11', 'twelve', '13', '14', '15', '<!-- ⋯ 15 unchanged lines -->'],
            Book::changes($lines, $new, 'html'),
        );
    }

    public function testTheVolumeIsNamedOnTheTitlePage(): void
    {
        $front = Book::front('en', '8.6', [[61, 'Passwords'], [62, 'Login']], ['name' => 'A', 'homepage' => 'https://example.org'], '2026-11-19', 2);
        self::assertStringContainsString('PHP 8.6 · Volume 2 · chapters 61–62</p>', $front);
    }

    public function testAnExcerptShowsItsLinesAndMarksTheRest(): void
    {
        $lines = array_map(static fn(int $i): string => "line $i", range(1, 10));
        self::assertSame(['// ⋯ 3 lines left out', 'line 4', 'line 5', '// ⋯ 2 lines left out', 'line 8', '// ⋯ 2 lines left out'], Book::excerpt($lines, Book::ranges('4-5, 8'), 'php'));
    }

    public function testABadRangeStopsTheBuild(): void
    {
        $this->expectExceptionMessage('the file has 10 lines');
        Book::excerpt(array_fill(0, 10, 'x'), Book::ranges('8-12'), 'php');
    }

    public function testRequestsKeepTheFilesCommentsAndTheChosenRequests(): void
    {
        $http = ['# the setup', '# run php shelf.php import', 'GET /', '', '###', '# two', 'GET /add', '', '###', '# three', 'GET /login'];
        self::assertSame(
            ['# the setup', '# run php shelf.php import', '# ⋯ requests left out: 2', '', '# three', 'GET /login'],
            Book::requests($http, Book::ranges('3'), 'en'),
        );
    }

    public function testExchangesKeepTheChosenAnswers(): void
    {
        $out = ['$ php shelf.php import', 'Imported 4 books', '', '> GET /', '', '< HTTP/1.1 200 OK', '', '> GET /add', '', '< HTTP/1.1 303 See Other', ''];
        self::assertSame(
            ['⋯ 3 lines left out', '⋯ requests left out: 1', '', '> GET /add', '', '< HTTP/1.1 303 See Other', ''],
            Book::exchanges($out, Book::ranges('2'), 'en'),
        );
    }

    public function testAWebPageShowsTheStartOfItsMainOnly(): void
    {
        $page = ['> GET /', '', '< HTTP/1.1 200 OK', '', '<!doctype html>', '<html>', '<head><title>Shelf</title></head>', '<body>', '<nav>…</nav>', '<main>', '<h1>Books</h1>', '</main>', '<footer>…</footer>', '</body>', '</html>'];
        self::assertSame(
            "> GET /\n\n< HTTP/1.1 200 OK\n\n⋯ 5 lines of the page left out\n<main>\n<h1>Books</h1>\n</main>\n⋯ 3 lines of the page left out",
            Book::mainOnly(implode("\n", $page)),
        );
    }

    public function testTheShortEditionRefusesLongListingsAndTheExtendedShowsThemWhole(): void
    {
        $this->shelfFile('41-first-tests', "<?php\n" . str_repeat("echo 1;\n", 90));
        $shown = [];
        $short = Book::structure(Book::html(Book::expand($this->root, '<!-- include: code/41-first-tests/shelf/src/Loan.php -->', 'en', $shown)), 'en');
        self::assertSame(['code/41-first-tests/shelf/src/Loan.php: 91 lines'], Book::tooLong($short));

        $shown = [];
        $cut = Book::expand($this->root, '<!-- include: code/41-first-tests/shelf/src/Loan.php lines 1-5 -->', 'en', $shown);
        self::assertStringContainsString('`code/41-first-tests/shelf/src/Loan.php` · excerpt', $cut);
        self::assertSame([], Book::tooLong(Book::structure(Book::html($cut), 'en')));

        $whole = Book::expand($this->root, '<!-- include: code/41-first-tests/shelf/src/Loan.php lines 1-5 -->');
        self::assertSame(90, substr_count($whole, 'echo 1;'), 'the extended edition ignores the excerpt');
    }

    private function shelfFile(string $chapter, string $code): void
    {
        mkdir("$this->root/code/$chapter/shelf/src", 0777, true);
        file_put_contents("$this->root/code/$chapter/shelf/src/Loan.php", $code);
        $this->extra[] = "$this->root/code/$chapter";
    }
}
