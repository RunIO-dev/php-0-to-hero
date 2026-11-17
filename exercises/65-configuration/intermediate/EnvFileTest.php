<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'envfile.php');

final class EnvFileTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = sys_get_temp_dir() . '/library-' . bin2hex(random_bytes(4)) . '.env';
    }

    protected function tearDown(): void
    {
        if (is_file($this->file)) {
            unlink($this->file);
        }
    }

    public function testSettingsCommentsAndQuotes(): void
    {
        file_put_contents($this->file, "# The library\n\n  LIBRARY_NAME=\"North Library\"  \n   # indented comment\nLOAN_DAYS=14\nDB_PASSWORD=a=b\"c\nLOAN_DAYS=21\n");

        self::assertSame(['LIBRARY_NAME' => 'North Library', 'LOAN_DAYS' => '21', 'DB_PASSWORD' => 'a=b"c'], readEnvFile($this->file));
    }

    public function testAWrongLineIsNamed(): void
    {
        file_put_contents($this->file, "LIBRARY_NAME=North\n\nlibrary name: North\n");

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage(basename($this->file) . ' line 3: expected NAME=value');

        readEnvFile($this->file);
    }
}
