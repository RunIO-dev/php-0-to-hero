<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ExportTest extends TestCase
{
    public function testIndentedWithLettersAsTheyAre(): void
    {
        require_once subject(__DIR__, 'export.php');

        self::assertSame(
            "[\n    {\n        \"title\": \"Schöne neue Welt\",\n        \"year\": 1932\n    }\n]",
            booksToJson([['title' => 'Schöne neue Welt', 'year' => 1932]]),
        );
    }

    public function testThrowsInsteadOfFalse(): void
    {
        require_once subject(__DIR__, 'export.php');

        $this->expectException(JsonException::class);
        booksToJson([['title' => "\xB1\x31"]]); // not valid UTF-8 text
    }
}
