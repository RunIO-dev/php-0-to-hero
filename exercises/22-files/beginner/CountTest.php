<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CountTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = (string) tempnam(sys_get_temp_dir(), 'titles-');
    }

    protected function tearDown(): void
    {
        unlink($this->path);
    }

    public function testCountsTheTitles(): void
    {
        require_once subject(__DIR__, 'count.php');
        file_put_contents($this->path, "Kindred\nKitchen\nBeloved\n");

        self::assertSame(3, countTitles($this->path));
    }

    public function testSkipsEmptyLines(): void
    {
        require_once subject(__DIR__, 'count.php');
        file_put_contents($this->path, "Kindred\n\nKitchen\n\n");

        self::assertSame(2, countTitles($this->path));
    }

    public function testAnEmptyFileHasNone(): void
    {
        require_once subject(__DIR__, 'count.php');
        file_put_contents($this->path, '');

        self::assertSame(0, countTitles($this->path));
    }
}
