<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shelf\Web\Covers;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CoversTest extends TestCase
{
    private string $dir;
    private Covers $covers;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/shelf-covers-' . bin2hex(random_bytes(4));
        $this->covers = new Covers($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob("$this->dir/*") ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
    }

    public function testAnImageIsStoredUnderAHashOfTheTitle(): void
    {
        $this->covers->save('Kindred', self::upload(__DIR__ . '/cover.png'));

        $cover = $this->covers->find('  KINDRED ');
        self::assertNotNull($cover);
        self::assertSame(['type' => 'image/png', 'path' => "$this->dir/" . hash('sha256', 'kindred') . '.png'], $cover);
        self::assertFileEquals(__DIR__ . '/cover.png', $cover['path']);
        self::assertNull($this->covers->find('Sula'));
    }

    /** @return iterable<string, array{UploadedFile, string}> */
    public static function refused(): iterable
    {
        yield 'code named .png' => [self::upload(__DIR__ . '/not-a-cover.png'), 'A cover must be a PNG or JPEG image.'];
        yield 'too large for PHP' => [self::upload('', UPLOAD_ERR_INI_SIZE), 'The cover is larger than 2 MB.'];
        yield 'cut off' => [self::upload('', UPLOAD_ERR_PARTIAL), 'The cover could not be uploaded. Please try again.'];
    }

    #[DataProvider('refused')]
    public function testOnlyImagesAreAccepted(UploadedFile $upload, string $reason): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($reason);

        $this->covers->save('Kindred', $upload);
    }

    public function testALargeFileIsRefusedByItsSize(): void
    {
        $big = tempnam(sys_get_temp_dir(), 'cover');
        self::assertIsString($big);
        file_put_contents($big, (string) file_get_contents(__DIR__ . '/cover.png') . str_repeat("\0", Covers::MAX_BYTES));
        try {
            $this->covers->check(self::upload($big));
            self::fail('A file over 2 MB should be refused');
        } catch (InvalidArgumentException $e) {
            self::assertSame('The cover is larger than 2 MB.', $e->getMessage());
        } finally {
            unlink($big);
        }
    }

    /** A file as PHP would hand it over; test: true, as it didn't come with a real request. */
    private static function upload(string $path, int $error = UPLOAD_ERR_OK): UploadedFile
    {
        return new UploadedFile($path, 'cover.png', null, $error, test: true);
    }
}
