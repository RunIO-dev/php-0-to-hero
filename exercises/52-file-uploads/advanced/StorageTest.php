<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'storage.php');

final class StorageTest extends TestCase
{
    private const PNG = "\x89PNG\r\n\x1a\n\0\0\0\rIHDR\0\0\0\x01\0\0\0\x01\x08\x02\0\0\0";
    private const JPEG = "\xFF\xD8\xFF\xE0\0\x10JFIF\0\x01\x01\0\0\x01\0\x01\0\0";

    private string $dir;
    private Storage $storage;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/storage-' . bin2hex(random_bytes(4));
        mkdir("$this->dir/in", 0o777, true);
        mkdir("$this->dir/store");
        $this->storage = new Storage("$this->dir/store");
    }

    protected function tearDown(): void
    {
        foreach ([...glob("$this->dir/in/*") ?: [], ...glob("$this->dir/store/*") ?: []] as $file) {
            unlink($file);
        }
        rmdir("$this->dir/in");
        rmdir("$this->dir/store");
        rmdir($this->dir);
    }

    private function upload(string $name, string $bytes): string
    {
        file_put_contents("$this->dir/in/$name", $bytes);

        return "$this->dir/in/$name";
    }

    public function testAnImageGetsANewRandomName(): void
    {
        $first = $this->storage->save($this->upload('my cover.png', self::PNG));
        $second = $this->storage->save($this->upload('my cover.png', self::PNG));

        self::assertMatchesRegularExpression('/^[0-9a-f]{32}\.png$/', $first);
        self::assertNotSame($first, $second);
        self::assertStringEqualsFile("$this->dir/store/$first", self::PNG);
    }

    public function testTheContentDecidesTheExtension(): void
    {
        $name = $this->storage->save($this->upload('photo.png', self::JPEG));

        self::assertStringEndsWith('.jpg', $name);
        self::assertSame(['type' => 'image/jpeg', 'path' => "$this->dir/store/$name"], $this->storage->open($name));
    }

    public function testOtherFilesAreRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->storage->save($this->upload('cover.png', "<?php echo 'hi';\n"));
    }

    /** @return iterable<string, array{string}> */
    public static function otherNames(): iterable
    {
        yield 'a folder up' => ['../in/cover.png'];
        yield 'code' => ['0123456789abcdef0123456789abcdef.php'];
        yield 'a short name' => ['cover.png'];
        yield 'upper case' => ['0123456789ABCDEF0123456789ABCDEF.png'];
        yield 'not stored' => ['0123456789abcdef0123456789abcdef.png'];
    }

    #[DataProvider('otherNames')]
    public function testOnlyStoredNamesCanBeOpened(string $name): void
    {
        file_put_contents("$this->dir/store/cover.png", self::PNG);
        file_put_contents("$this->dir/store/0123456789abcdef0123456789abcdef.php", '<?php');

        self::assertNull($this->storage->open($name));
    }
}
