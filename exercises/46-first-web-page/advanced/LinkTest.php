<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LinkTest extends TestCase
{
    public function testSafeLinks(): void
    {
        require_once subject(__DIR__, 'link.php');

        self::assertSame('<a href="https://www.php.net/">PHP</a>', safeLink('https://www.php.net/', 'PHP'));
        self::assertSame('<a href="/books.php?a=1&amp;b=2">Books &amp; more</a>', safeLink('/books.php?a=1&b=2', 'Books & more'));
        self::assertSame('<a href="https://example.org/&quot;onmouseover=&quot;x">x</a>', safeLink('https://example.org/"onmouseover="x', 'x'));
    }

    public function testDangerousAddressesBecomeText(): void
    {
        require_once subject(__DIR__, 'link.php');

        self::assertSame('Click &lt;me&gt;', safeLink('javascript:alert(1)', 'Click <me>'));
        self::assertSame('Away', safeLink('//evil.example', 'Away'));
        self::assertSame('Data', safeLink('data:text/html,<script>', 'Data'));
    }

    public function testASearchLink(): void
    {
        require_once subject(__DIR__, 'link.php');

        self::assertSame('<a href="/search.php?q=Tom+%26+Jerry">Tom &amp; Jerry</a>', searchLink('Tom & Jerry'));
        self::assertSame('<a href="/search.php?q=%3Cb%3E">&lt;b&gt;</a>', searchLink('<b>'));
    }
}
