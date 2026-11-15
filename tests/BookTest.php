<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tools\Book;

final class BookTest extends TestCase
{
    private string $root;

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
        self::assertStringContainsString('<figure class="listing ok"><figcaption><span class="dots"><i></i><i></i><i></i></span><span class="path">code/06-variables/name.php</span></figcaption><pre', $html);
        self::assertStringContainsString('<div class="out"><div class="out-h">Output</div><pre data-lang="txt"', $html);
        self::assertStringContainsString('<figure class="listing bad">', $html);
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
}
