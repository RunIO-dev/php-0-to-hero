<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tools\Batches;
use Tools\Projects;
use Tools\Run;

/** Shelf as a Composer project inside the course (chapter 36 on). */
final class ProjectsTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/projects-' . bin2hex(random_bytes(4));
        $this->put('tools/verify', '');
        $this->put('composer.json', '{}');
        $this->put('code/36-x/shelf/composer.json', '{"autoload": {"psr-4": {"Shelf\\\\": "src/"}}}');
        $this->put('code/36-x/shelf/shelf.php', "<?php\nrequire __DIR__ . '/vendor/autoload.php';\necho (new Shelf\\Book())->title(), \"\\n\";\n");
        $this->put('code/36-x/shelf/src/Book.php', "<?php\nnamespace Shelf;\nfinal class Book { public function title(): string { return 'Kindred'; } }\n");
        $this->put('code/36-x/plain.php', "<?php\nfinal class Book {}\n");
    }

    protected function tearDown(): void
    {
        Run::remove($this->root);
    }

    private function put(string $path, string $content): void
    {
        $dir = dirname($this->root . '/' . $path);
        if (!is_dir($dir)) {
            mkdir($dir, 0o777, true);
        }
        file_put_contents($this->root . '/' . $path, $content);
    }

    public function testFindsTheProjectButNotTheCourse(): void
    {
        self::assertSame($this->root . '/code/36-x/shelf', Projects::of($this->root . '/code/36-x/shelf/src/Book.php'));
        self::assertSame($this->root . '/code/36-x/shelf', Projects::of($this->root . '/code/36-x/shelf/shelf.php'));
        self::assertNull(Projects::of($this->root . '/code/36-x/plain.php'));
    }

    public function testOnlyTheClassesBelowAreAutoloaded(): void
    {
        self::assertTrue(Projects::isAutoloaded($this->root . '/code/36-x/shelf/src/Book.php'));
        self::assertFalse(Projects::isAutoloaded($this->root . '/code/36-x/shelf/shelf.php'));
        self::assertFalse(Projects::isAutoloaded($this->root . '/code/36-x/plain.php'));
    }

    public function testAProjectIsOneBatch(): void
    {
        $files = ["$this->root/code/36-x/plain.php", "$this->root/code/36-x/shelf/shelf.php", "$this->root/code/36-x/shelf/src/Book.php"];

        self::assertSame(
            [["$this->root/code/36-x/plain.php"], ["$this->root/code/36-x/shelf/shelf.php", "$this->root/code/36-x/shelf/src/Book.php"]],
            Batches::split($files),
        );
    }

    public function testPrepareWritesTheAutoloader(): void
    {
        self::assertSame([], Projects::prepare($this->root));
        self::assertFileExists($this->root . '/code/36-x/shelf/vendor/autoload.php');

        $run = Run::capture([PHP_BINARY, 'shelf.php'], $this->root . '/code/36-x/shelf');
        self::assertSame("Kindred\n", $run['out']);
    }
}
