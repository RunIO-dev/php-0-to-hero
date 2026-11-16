<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LogTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir() . '/returns-' . bin2hex(random_bytes(4)) . '.log';
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function testCreatesTheFile(): void
    {
        require_once subject(__DIR__, 'log.php');
        logReturn($this->path, 'Kindred', new DateTimeImmutable('2026-11-17'));

        self::assertSame("2026-11-17 Kindred\n", file_get_contents($this->path));
    }

    public function testAddsToTheEnd(): void
    {
        require_once subject(__DIR__, 'log.php');
        logReturn($this->path, 'Kindred', new DateTimeImmutable('2026-11-17'));
        logReturn($this->path, 'Kitchen', new DateTimeImmutable('2026-11-18'));

        self::assertSame("2026-11-17 Kindred\n2026-11-18 Kitchen\n", file_get_contents($this->path));
    }
}
