<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SettingsTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $this->path = (string) tempnam(sys_get_temp_dir(), 'settings-');
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    private function load(string $json): mixed
    {
        require_once subject(__DIR__, 'settings.php');
        file_put_contents($this->path, $json);

        return loadSettings($this->path);
    }

    public function testReadsBothSettings(): void
    {
        self::assertSame(['loanDays' => 21, 'feePerDay' => 30], $this->load('{"loanDays": 21, "feePerDay": 30}'));
    }

    public function testMissingSettingsGetTheirDefaults(): void
    {
        self::assertSame(['loanDays' => 7, 'feePerDay' => 50], $this->load('{"loanDays": 7}'));
        self::assertSame(['loanDays' => 14, 'feePerDay' => 50], $this->load('{}'));
    }

    public function testAWrongTypeIsNamed(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('feePerDay');
        $this->load('{"loanDays": 21, "feePerDay": "30"}');
    }

    public function testBrokenJsonThrows(): void
    {
        $this->expectException(JsonException::class);
        $this->load('{"loanDays": 21,}');
    }

    public function testAMissingFileThrows(): void
    {
        require_once subject(__DIR__, 'settings.php');
        unlink($this->path);

        $this->expectException(RuntimeException::class);
        loadSettings($this->path);
    }
}
