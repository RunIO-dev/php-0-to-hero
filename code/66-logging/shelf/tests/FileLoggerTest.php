<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shelf\FileLogger;

final class FileLoggerTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/shelf-log-' . bin2hex(random_bytes(4)) . '/shelf.log';
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
        if (is_dir(dirname($this->file))) {
            rmdir(dirname($this->file));
        }
    }

    public function testOneLinePerMessageWithThePlaceholdersFilledIn(): void
    {
        $logger = new FileLogger($this->file, new DateTimeImmutable('2026-03-16 10:00:00 UTC'));

        $logger->info('{name} logged in', ['name' => 'Amira']);
        $logger->error('The cover was not saved', ['title' => 'Sula', 'exception' => new RuntimeException('Disk full')]);

        $lines = file($this->file, FILE_IGNORE_NEW_LINES) ?: [];
        self::assertSame('2026-03-16T10:00:00+00:00 INFO Amira logged in', $lines[0] ?? null);
        self::assertStringStartsWith('2026-03-16T10:00:00+00:00 ERROR The cover was not saved {"title":"Sula","exception":"RuntimeException: Disk full at ', $lines[1] ?? '');
    }

    public function testALineBreakInInputStaysInItsLine(): void
    {
        $logger = new FileLogger($this->file, new DateTimeImmutable('2026-03-16 10:00:00 UTC'));

        $logger->warning('Failed login for {name}', ['name' => "Amira\n2026-03-16T10:00:01+00:00 INFO Jonas logged in"]);

        self::assertCount(1, file($this->file) ?: []);
        self::assertStringContainsString('Amira\n2026', (string) file_get_contents($this->file));
    }
}
