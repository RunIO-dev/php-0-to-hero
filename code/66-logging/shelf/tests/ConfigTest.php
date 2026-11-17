<?php

declare(strict_types=1);

namespace Shelf\Tests;

use PHPUnit\Framework\TestCase;
use Shelf\Config;
use UnexpectedValueException;

final class ConfigTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/shelf-config-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
        file_put_contents("$this->dir/.env", "# in Git\nSHELF_ENV=development\nSHELF_DATABASE=var/shelf.sqlite\nSHELF_COVERS=\"storage/covers\"\nSHELF_LOG=var/log/shelf.log\n");
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob("$this->dir/.env*") ?: []);
        rmdir($this->dir);
        putenv('SHELF_DATABASE'); // unset
    }

    public function testTheSettingsComeFromDotEnv(): void
    {
        $config = Config::load($this->dir);

        self::assertEquals(new Config('development', "$this->dir/var/shelf.sqlite", "$this->dir/storage/covers", "$this->dir/var/log/shelf.log"), $config);
        self::assertFalse($config->isProduction());
    }

    public function testTheLocalFileAndThenTheEnvironmentWin(): void
    {
        file_put_contents("$this->dir/.env.local", "SHELF_ENV=production\nSHELF_DATABASE=/home/amira/shelf.sqlite\n");
        putenv('SHELF_DATABASE=/srv/shelf/shelf.sqlite');

        $config = Config::load($this->dir);

        self::assertTrue($config->isProduction());
        self::assertSame('/srv/shelf/shelf.sqlite', $config->database);
    }

    public function testAMissingSettingIsNamed(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The setting SHELF_COVERS is missing');

        Config::fromSettings(['SHELF_ENV' => 'development', 'SHELF_DATABASE' => 'var/shelf.sqlite', 'SHELF_COVERS' => ' ', 'SHELF_LOG' => 'var/log/shelf.log'], $this->dir);
    }

    public function testAnUnknownEnvironmentIsRefused(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('SHELF_ENV must be development or production, not "prod"');

        Config::fromSettings(['SHELF_ENV' => 'prod', 'SHELF_DATABASE' => 'a', 'SHELF_COVERS' => 'b', 'SHELF_LOG' => 'c'], $this->dir);
    }
}
