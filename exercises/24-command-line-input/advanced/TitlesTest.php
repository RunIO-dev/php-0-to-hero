<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TitlesTest extends TestCase
{
    public function testCleansSortsAndCounts(): void
    {
        self::assertSame(
            "1. Beloved\n2. Kindred\n3. Things Fall Apart\n3 titles\n",
            output_of(subject(__DIR__, 'titles.php'), [], "  Kindred \n\nThings Fall Apart\nBeloved\nkindred\nbeloved\n"),
        );
    }

    public function testKeepsTheFirstSpelling(): void
    {
        self::assertSame("1. kitchen\n1 title\n", output_of(subject(__DIR__, 'titles.php'), [], "kitchen\nKitchen\nKITCHEN\n"));
    }

    public function testNoTitles(): void
    {
        self::assertSame("No titles\n", output_of(subject(__DIR__, 'titles.php'), [], "\n  \n"));
        self::assertSame("No titles\n", output_of(subject(__DIR__, 'titles.php'), [], ''));
    }
}
