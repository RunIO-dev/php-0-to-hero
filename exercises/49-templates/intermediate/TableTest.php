<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    private function render(string $caption, string ...$titles): string
    {
        $template = require subject(__DIR__, 'table.php');
        ob_start();
        $template($caption, ...$titles);

        return trim((string) ob_get_clean());
    }

    public function testOneRowPerTitleEscaped(): void
    {
        $html = $this->render('New & old', 'Kindred', 'Tom & Jerry');

        self::assertStringContainsString('<caption>New &amp; old</caption>', $html);
        self::assertSame(2, substr_count($html, '<tr>'));
        self::assertLessThan(strpos($html, 'Tom &amp; Jerry'), strpos($html, 'Kindred'));
    }

    public function testNoTitlesShowAMessageInstead(): void
    {
        $html = $this->render('Books');

        self::assertSame('<p class="empty">No books yet.</p>', $html);
    }

    public function testTheTemplateUsesTheAlternativeSyntax(): void
    {
        $code = (string) file_get_contents(subject(__DIR__, 'table.php'));

        self::assertStringContainsString('endif', $code);
        self::assertStringContainsString('endforeach', $code);
    }
}
