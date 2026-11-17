<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'JsonLinesLogger.php');

final class JsonLinesLoggerTest extends TestCase
{
    private string $file;
    private JsonLinesLogger $log;

    protected function setUp(): void
    {
        $this->file = (string) tempnam(sys_get_temp_dir(), 'log-');
        $this->log = new JsonLinesLogger($this->file, new DateTimeImmutable('2026-03-16 10:00:00 UTC'));
    }

    protected function tearDown(): void
    {
        unlink($this->file);
    }

    public function testOneJsonObjectPerLine(): void
    {
        // In single quotes, \n is a backslash and an n: what json_encode writes for a line break.
        $this->log->info('{member} borrowed {title}', ['member' => 'Amira', 'title' => 'Kindred']);
        $this->log->warning('Failed login for {name}', ['name' => "Jonas\nfake line", 'tries' => 3]);

        self::assertSame(
            '{"time":"2026-03-16T10:00:00+00:00","level":"info","message":"Amira borrowed Kindred","context":{"member":"Amira","title":"Kindred"}}' . "\n"
            . '{"time":"2026-03-16T10:00:00+00:00","level":"warning","message":"Failed login for Jonas\nfake line","context":{"name":"Jonas\nfake line","tries":3}}' . "\n",
            (string) file_get_contents($this->file),
        );
    }

    public function testAnExceptionBecomesItsClassAndMessage(): void
    {
        $this->log->error('Cover not saved', ['exception' => new RuntimeException('Disk full')]);

        $line = json_decode((string) file_get_contents($this->file), true);
        self::assertSame(['exception' => 'RuntimeException: Disk full'], is_array($line) ? $line['context'] : null);
    }
}
