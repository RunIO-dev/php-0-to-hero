<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ImportTest extends TestCase
{
    public function testReadsTheTitles(): void
    {
        require_once subject(__DIR__, 'import.php');

        self::assertSame(['Kindred', 'Kitchen'], titlesFromJson('[{"title": "Kindred", "year": 1979}, {"title": "Kitchen"}]'));
        self::assertSame([], titlesFromJson('[]'));
    }

    public function testBrokenJsonThrows(): void
    {
        require_once subject(__DIR__, 'import.php');

        $this->expectException(JsonException::class);
        titlesFromJson('[{"title": "Kindred"},]');
    }
}
