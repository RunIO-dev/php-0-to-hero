<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tools\Batches;
use Tools\Projects;
use Tools\Run;
use Tools\Shelf;

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

    public function testShelfWithItsOwnConfigurationIsAnalysedOnItsOwn(): void
    {
        self::assertFalse(Projects::hasOwnAnalysis($this->root . '/code/36-x/shelf/src/Book.php'));
        $this->put('code/40-y/shelf/phpstan.neon.dist', "parameters:\n    level: 10\n    paths: [src]\n");
        $this->put('code/41-z/shelf/src/Book.php', "<?php\nnamespace Shelf;\nfinal class Book { public function title(): string { return 'Sula'; } }\n");
        self::assertFalse(Projects::hasOwnAnalysis($this->root . '/code/36-x/shelf/src/Book.php'), 'not yet in chapter 36');
        self::assertTrue(Projects::hasOwnAnalysis($this->root . '/code/41-z/shelf/src/Book.php'), 'since chapter 40');
        $this->put('shelf/composer.json', '{}');
        $this->put('shelf/phpstan.neon.dist', '');
        self::assertTrue(Projects::hasOwnAnalysis($this->root . '/shelf/src/Book.php'));
    }

    public function testShelfTestsRunInTheStateAfterEachChapter(): void
    {
        symlink(dirname(__DIR__) . '/vendor', $this->root . '/vendor');
        $config = '<phpunit bootstrap="vendor/autoload.php"><testsuites><testsuite name="shelf"><directory>tests</directory></testsuite></testsuites></phpunit>';
        $test = "<?php\nuse PHPUnit\\Framework\\TestCase;\nfinal class BookTest extends TestCase\n{\n    public function testTitle(): void\n    {\n        self::assertSame('%s', (new Shelf\\Book())->title());\n    }\n}\n";
        // chapter 41 adds only the tests: Book and composer.json come from chapter 36
        $this->put('code/41-z/shelf/phpunit.xml.dist', $config);
        $this->put('code/41-z/shelf/tests/BookTest.php', sprintf($test, 'Kindred'));
        self::assertSame([], Projects::test($this->root));

        $this->put('code/41-z/shelf/tests/BookTest.php', sprintf($test, 'Sula'));
        $problems = Projects::test($this->root);
        self::assertCount(1, $problems);
        self::assertStringStartsWith('code/41-z/shelf: ', $problems[0]);
        self::assertStringContainsString('FAILURES', $problems[0]);
    }

    public function testTheStateHasTheNewestFileUpToTheChapter(): void
    {
        $this->put('code/41-z/shelf/src/Book.php', 'new');
        $this->put('code/42-z/shelf/src/Book.php', 'newer');

        $state = Shelf::state($this->root, $this->root . '/code/41-z/shelf');
        self::assertSame($this->root . '/code/41-z/shelf/src/Book.php', $state['src/Book.php']);
        self::assertSame($this->root . '/code/36-x/shelf/shelf.php', $state['shelf.php']);
        self::assertSame(['composer.json', 'shelf.php', 'src/Book.php'], array_keys($state));
    }

    public function testARemovedFileLeavesTheStateFromItsChapterOn(): void
    {
        $this->put('code/41-z/shelf/src/Book.php', 'new');
        $this->put('code/42-z/shelf/src/Book.php.removed', '');

        self::assertArrayHasKey('src/Book.php', Shelf::state($this->root, $this->root . '/code/41-z/shelf'));
        self::assertSame(['composer.json', 'shelf.php'], array_keys(Shelf::state($this->root, $this->root . '/code/42-z/shelf')));
    }

    public function testASnapshotWithoutComposerJsonIsPartOfShelf(): void
    {
        $this->put('code/42-w/shelf/tests/BookTest.php', "<?php\n");
        $this->put('code/42-w/shelf/shelf.php', "<?php\n");

        self::assertTrue(Projects::isAutoloaded($this->root . '/code/42-w/shelf/tests/BookTest.php'));
        self::assertFalse(Projects::isAutoloaded($this->root . '/code/42-w/shelf/shelf.php'));
    }
}
