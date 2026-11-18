<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'AccessibilityCheck.php');

final class AccessibilityCheckTest extends TestCase
{
    private static function page(string $body, string $lang = 'en'): string
    {
        return "<!doctype html><html lang=\"$lang\"><head><title>Shelf</title></head><body>$body</body></html>";
    }

    public function testAGoodPageHasNoProblems(): void
    {
        self::assertSame([], AccessibilityCheck::problems(self::page(<<<'HTML'
            <h1>Books</h1>
            <h2>New</h2><h3>This week</h3><h2>Popular</h2>
            <img src="cover.png" alt=""><img src="logo.png" alt="Shelf">
            <form><label>Title <input name="title"></label>
            <label for="year">Year</label><input id="year" name="year">
            <input type="hidden" name="_token" value="x"><input type="submit" value="Search"></form>
            <a href="/">Books</a> <a href="/next" aria-label="Next page">→</a>
            HTML)));
    }

    public function testThePageNeedsALanguage(): void
    {
        self::assertSame(['no lang'], AccessibilityCheck::problems(self::page('<h1>Books</h1>', '')));
        self::assertSame(['no lang'], AccessibilityCheck::problems('<!doctype html><html><body><h1>Books</h1></body></html>'));
    }

    public function testExactlyOneMainHeading(): void
    {
        self::assertSame(['h1: 0'], AccessibilityCheck::problems(self::page('<h2>Books</h2>')));
        self::assertSame(['h1: 2'], AccessibilityCheck::problems(self::page('<h1>Books</h1><h1>Loans</h1>')));
    }

    public function testHeadingsGoDownOneLevelAtATime(): void
    {
        self::assertSame(
            ['heading skips: h2 to h4'],
            AccessibilityCheck::problems(self::page('<h1>Books</h1><h2>New</h2><h4>Today</h4><h2>Old</h2>')),
        );
        self::assertSame(['heading skips: h1 to h3'], AccessibilityCheck::problems(self::page('<h1>Books</h1><h3>New</h3>')));
        self::assertSame([], AccessibilityCheck::problems(self::page('<h1>Books</h1><h2>New</h2><h3>Today</h3><h2>Old</h2>')));
    }

    public function testEveryImageHasAnAlt(): void
    {
        self::assertSame(
            ['image without alt: cover.png'],
            AccessibilityCheck::problems(self::page('<h1>Books</h1><img src="cover.png"><img src="logo.png" alt="">')),
        );
    }

    public function testEveryFieldHasALabel(): void
    {
        self::assertSame(
            ['field without label: email', 'field without label: role', 'field without label: note', 'field without label: year'],
            AccessibilityCheck::problems(self::page(<<<'HTML'
                <h1>Join</h1>
                <input name="email" placeholder="Email">
                <select name="role"><option>reader</option></select>
                <textarea name="note"></textarea>
                <label for="title">Title</label><input id="year" name="year">
                <input type="hidden" name="_token"><input type="button" value="Go">
                HTML)),
        );
    }

    public function testALinkNeedsWordsForAScreenReader(): void
    {
        self::assertSame(
            ['link without text: /next', 'link without text: /'],
            AccessibilityCheck::problems(self::page('<h1>Books</h1><a href="/next">  </a><a href="/"><img src="logo.svg" alt=""></a><a href="/x">Next</a>')),
        );
    }

    public function testProblemsComeInOrder(): void
    {
        self::assertSame(
            ['no lang', 'h1: 0', 'image without alt: a.png', 'field without label: q', 'link without text: /'],
            AccessibilityCheck::problems(self::page('<img src="a.png"><input name="q"><a href="/"></a>', '')),
        );
    }
}
