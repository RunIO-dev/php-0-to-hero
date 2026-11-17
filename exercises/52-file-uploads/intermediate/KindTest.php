<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'kind.php');

final class KindTest extends TestCase
{
    /** @return iterable<string, array{string, string, ?string}> name, the file's first bytes, the answer */
    public static function files(): iterable
    {
        yield 'a PNG' => ['cover.png', "\x89PNG\r\n\x1a\n\0\0\0\rIHDR\0\0\0\x01\0\0\0\x01\x08\x02\0\0\0", 'png'];
        yield 'a JPEG' => ['cover.jpeg', "\xFF\xD8\xFF\xE0\0\x10JFIF\0\x01\x01\0\0\x01\0\x01\0\0", 'jpg'];
        yield 'a WebP' => ['cover.webp', "RIFF\x24\0\0\0WEBPVP8 \x18\0\0\0", 'webp'];
        yield 'a GIF' => ['cover.gif', "GIF89a\x01\0\x01\0\0\0\0;", null];
        yield 'a PDF' => ['cover.pdf', "%PDF-1.7\n1 0 obj\n", null];
        yield 'PHP named .png' => ['cover.png', "<?php echo 'hi';\n", null];
        yield 'SVG with a script' => ['cover.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', null];
    }

    #[DataProvider('files')]
    public function testTheContentDecides(string $name, string $bytes, ?string $extension): void
    {
        $dir = sys_get_temp_dir() . '/kind-' . bin2hex(random_bytes(4));
        mkdir($dir);
        file_put_contents("$dir/$name", $bytes);
        try {
            self::assertSame($extension, imageExtension("$dir/$name"));
        } finally {
            unlink("$dir/$name");
            rmdir($dir);
        }
    }
}
