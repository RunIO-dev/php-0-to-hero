<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReceiptTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = subject(__DIR__, 'receipt.php');
    }

    public function testPrintsTheReceiptExactly(): void
    {
        self::assertSame("Shelf library\n-------------\nLoans: 3\nDue:   2026-12-01\n", output_of($this->file));
    }

    public function testHasAComment(): void
    {
        $comments = array_filter(PhpToken::tokenize((string) file_get_contents($this->file)), static fn(PhpToken $t): bool => $t->is([T_COMMENT, T_DOC_COMMENT]));

        self::assertNotEmpty($comments, 'Keep at least one comment.');
    }

    public function testHasNoClosingTag(): void
    {
        self::assertStringNotContainsString('?>', (string) file_get_contents($this->file), 'A file of pure PHP has no closing tag.');
    }
}
