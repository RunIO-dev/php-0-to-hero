<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tools\Analysis;
use Tools\Chapters;
use Tools\Examples;
use Tools\Exercises;
use Tools\Glossary;
use Tools\Shelf;

/** Each test builds a tiny repo in a temp folder and runs one check on it. */
final class ChecksTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/p0h-' . bin2hex(random_bytes(4));
        mkdir($this->root);
        symlink(dirname(__DIR__) . '/vendor', $this->root . '/vendor');
        copy(dirname(__DIR__) . '/phpunit.xml.dist', $this->root . '/phpunit.xml.dist');
        $this->put('tools/bootstrap.php', (string) file_get_contents(dirname(__DIR__) . '/tools/bootstrap.php'));
    }

    protected function tearDown(): void
    {
        unlink($this->root . '/vendor');
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir((string) $item) : unlink((string) $item);
        }
        rmdir($this->root);
    }

    private function put(string $path, string $content): void
    {
        $dir = dirname($this->root . '/' . $path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($this->root . '/' . $path, $content);
    }

    /** @param list<string> $headings */
    private function chapter(array $headings, string $body = ''): string
    {
        return "# Title\n\n" . implode('', array_map(static fn(string $h): string => "## $h\n\ntext\n\n", $headings)) . $body;
    }

    public function testChapterWithTemplateHeadingsPasses(): void
    {
        $this->put('code/06-variables/a.php', '<?php');
        $this->put('book/en/06-variables.md', $this->chapter(Chapters::HEADINGS['en'], "<!-- include: code/06-variables/a.php -->\n"));
        self::assertSame([], Chapters::check($this->root));
    }

    public function testChapterMissingHeadingFails(): void
    {
        $this->put('book/en/06-variables.md', $this->chapter(array_slice(Chapters::HEADINGS['en'], 1)));
        self::assertSame(['book/en/06-variables.md: the ## headings must be exactly the template (missing: Goals)'], Chapters::check($this->root));
    }

    public function testChapterHeadingsOutOfOrderFail(): void
    {
        $this->put('book/en/06-variables.md', $this->chapter(array_reverse(Chapters::HEADINGS['en'])));
        self::assertStringContainsString('wrong order', Chapters::check($this->root)[0]);
    }

    public function testMissingIncludeAndBadNameFail(): void
    {
        $this->put('book/en/variables.md', $this->chapter(Chapters::HEADINGS['en'], "<!-- include: code/nope.php -->\n<!-- include: ../secret -->\n"));
        self::assertSame([
            'book/en/variables.md: name must be NN-slug.md',
            'book/en/variables.md: include not found in code/ or shelf/: code/nope.php',
            'book/en/variables.md: include not found in code/ or shelf/: ../secret',
        ], Chapters::check($this->root));
    }

    public function testExampleMatchingItsOutputPasses(): void
    {
        $this->put('code/05-first/hello.php', "<?php\necho \"Hello, Amira!\\n\";\n");
        $this->put('code/05-first/hello.out', "Hello, Amira!\n");
        self::assertSame([], Examples::check($this->root));
    }

    public function testExampleOutputShowsWarningsWithoutLocalPaths(): void
    {
        $this->put('code/07-types/undefined.php', "<?php\necho \$name;\n");
        self::assertSame(
            "\nWarning: Undefined variable \$name in undefined.php on line 2\n",
            Examples::output($this->root, $this->root . '/code/07-types/undefined.php'),
        );
    }

    public function testExampleRunsWithTheArgumentsAndInputInItsRunFile(): void
    {
        $this->put('code/24-input/greet.php', "<?php\necho implode('|', array_slice(\$argv, 1)), ':', trim((string) fgets(STDIN)), \"\\n\";\n");
        $this->put('code/24-input/names.txt', "Amira\n");
        $this->put('code/24-input/greet.run', "php greet.php add \"Things Fall Apart\" '' < names.txt\n");

        self::assertSame("add|Things Fall Apart|:Amira\n", Examples::output($this->root, $this->root . '/code/24-input/greet.php'));
        self::assertSame("php greet.php add \"Things Fall Apart\" '' < names.txt", Examples::command($this->root . '/code/24-input/greet.php')['line']);
    }

    public function testRunFileMustRunThisFileWithInputFromTheSameFolder(): void
    {
        $this->put('code/24-input/greet.php', "<?php\n");
        $this->put('code/24-input/greet.run', "php other.php\n");
        try {
            Examples::command($this->root . '/code/24-input/greet.php');
            self::fail('a .run file for another script was accepted');
        } catch (\RuntimeException $e) {
            self::assertStringContainsString('php greet.php', $e->getMessage());
        }

        $this->put('code/24-input/greet.run', "php greet.php < ../../secret.txt\n");
        $this->expectException(\RuntimeException::class);
        Examples::command($this->root . '/code/24-input/greet.php');
    }

    public function testOutputOfPassesArgumentsAndTypedInput(): void
    {
        $this->put('exercises/24-input/beginner/add.php', "<?php\necho \$argv[1] ?? '-', ' ', trim((string) fgets(STDIN)), \"\\n\";\n");

        self::assertSame("lend Kindred\n", output_of($this->root . '/exercises/24-input/beginner/add.php', ['lend'], "Kindred\n"));
        self::assertSame("- \n", output_of($this->root . '/exercises/24-input/beginner/add.php'));
    }

    public function testARequiredFileOfFunctionsNeedsNoOutFile(): void
    {
        $this->put('code/26-review/format.php', "<?php\nfunction shout(string \$t): string { return strtoupper(\$t); }\n");
        $this->put('code/26-review/report.php', "<?php\nrequire_once __DIR__ . '/format.php';\necho shout('kindred'), \"\\n\";\n");
        $this->put('code/26-review/report.out', "KINDRED\n");

        self::assertSame([], Examples::check($this->root));
        self::assertSame(['format.php'], Examples::required($this->root . '/code/26-review'));
    }

    public function testExampleWithDifferentOutputOrNoOutFileFails(): void
    {
        $this->put('code/05-first/hello.php', "<?php\necho \"Hi\\n\";\n");
        $this->put('code/05-first/hello.out', "Hello\n");
        $this->put('code/05-first/other.php', "<?php\n");
        $problems = Examples::check($this->root);
        self::assertCount(2, $problems);
        self::assertStringContainsString("want: Hello\n    got:  Hi", $problems[0]);
        self::assertSame('code/05-first/other.php: no .out file next to it', $problems[1]);
    }

    public function testATestFileRunsWithPhpunitWithoutTheLinesThatChange(): void
    {
        $this->put('code/41-tests/fee.php', "<?php\nfunction fee(int \$days): int\n{\n    return \$days * 20;\n}\n");
        $this->put('code/41-tests/FeeTest.php', <<<'PHP'
            <?php
            use PHPUnit\Framework\TestCase;
            require_once __DIR__ . '/fee.php';
            final class FeeTest extends TestCase
            {
                public function testTwentyCentsADay(): void
                {
                    self::assertSame(100, fee(5));
                }
            }
            PHP);
        $out = Examples::output($this->root, $this->root . '/code/41-tests/FeeTest.php');

        self::assertSame("Fee\n ✔ Twenty cents a day\n\nOK (1 test, 1 assertion)\n", (string) preg_replace('/^\..*\n\n/', '', $out));
        self::assertStringNotContainsString($this->root, $out);
        self::assertSame('vendor/bin/phpunit --testdox code/41-tests/FeeTest.php', Examples::command($this->root . '/code/41-tests/FeeTest.php')['line']);
        $this->put('code/41-tests/FeeTest.out', $out);
        self::assertSame([], Examples::check($this->root), 'fee.php is loaded by the test, so it needs no .out');
    }

    public function testPhpstanOutputOfAnExampleIsChecked(): void
    {
        $this->put('code/40-analysis/upper.php', "<?php\necho strtoupper(rand(0, 1) === 1 ? 'kindred' : null), \"\\n\";\n");
        $command = '$ vendor/bin/phpstan analyse --level 8 code/40-analysis/upper.php';
        $this->put('code/40-analysis/upper.phpstan', "$command\nold message\n");
        self::assertTrue(Analysis::has($this->root . '/code/40-analysis/upper.php'));
        self::assertStringContainsString('PHPStan now prints something else', implode("\n", Analysis::check($this->root)));

        Analysis::write($this->root, $this->root . '/code/40-analysis/upper.php');
        $written = (string) file_get_contents($this->root . '/code/40-analysis/upper.phpstan');
        self::assertStringStartsWith("$command\n", $written);
        self::assertStringContainsString('strtoupper expects string,', $written);
        self::assertSame([], Analysis::check($this->root));

        $this->put('code/40-analysis/upper.phpstan', "\$ vendor/bin/phpstan analyse --level 8 code/40-analysis/other.php\n");
        self::assertStringContainsString('the first line must be', implode("\n", Analysis::check($this->root)));
    }

    public function testBadExampleIsOnlySyntaxChecked(): void
    {
        $this->put('code/46-web/echo.bad.php', "<?php\necho \$_GET['name'];\n");
        $this->put('code/46-web/broken.bad.php', "<?php\necho 'x'\n");
        $problems = Examples::check($this->root);
        self::assertCount(1, $problems);
        self::assertStringStartsWith('code/46-web/broken.bad.php: syntax error', $problems[0]);
    }

    private function exercise(string $starter, string $solution): void
    {
        $test = <<<'PHP'
            <?php
            declare(strict_types=1);
            use PHPUnit\Framework\TestCase;
            final class DoubleTest extends TestCase
            {
                public function testDoubles(): void
                {
                    require_once subject(__DIR__, 'double.php');
                    self::assertSame(8, double(4));
                }
            }
            PHP;
        $this->put('exercises/15-functions/beginner/DoubleTest.php', $test);
        $this->put('exercises/15-functions/beginner/double.php', $starter);
        $this->put('solutions/15-functions/beginner/double.php', $solution);
    }

    public function testRealExercisePasses(): void
    {
        $this->exercise(
            "<?php\nfunction double(int \$n): int\n{\n    return 0; // your code here\n}\n",
            "<?php\nfunction double(int \$n): int\n{\n    return \$n * 2;\n}\n",
        );
        self::assertSame([], Exercises::check($this->root));
    }

    public function testExerciseThatAsksNothingOrHasWrongSolutionFails(): void
    {
        $this->exercise(
            "<?php\nfunction double(int \$n): int\n{\n    return \$n * 2;\n}\n",
            "<?php\nfunction double(int \$n): int\n{\n    return \$n + 2;\n}\n",
        );
        $problems = Exercises::check($this->root);
        self::assertCount(2, $problems);
        self::assertStringContainsString('pass on the starter', $problems[0]);
        self::assertStringContainsString('fail on the solution', $problems[1]);
    }

    public function testSlowCommandIsStopped(): void
    {
        $run = \Tools\Run::capture([PHP_BINARY, '-r', 'fgets(STDIN); sleep(5);'], null, [], 1);
        self::assertSame(124, $run['code']);
        self::assertStringContainsString('Stopped after 1 seconds', $run['err']);
    }

    public function testSubjectMapsOnlyTheExerciseFolder(): void
    {
        putenv('EXERCISE_SOLUTION=1');
        $path = subject('/home/ana/exercises/php-0-to-hero/exercises/15-functions/beginner', 'due.php');
        putenv('EXERCISE_SOLUTION');
        self::assertSame('/home/ana/exercises/php-0-to-hero/solutions/15-functions/beginner/due.php', $path);
    }

    public function testArabicChapterWithRejectedTermFails(): void
    {
        $this->put('glossary/ar.json', json_encode(['terms' => [
            ['en' => 'array', 'ar' => 'مصفوفة', 'rejected' => ['صفيف']],
            ['en' => 'code', 'ar' => 'شيفرة', 'rejected' => ['كود']],
        ]], JSON_THROW_ON_ERROR));
        $this->put('book/ar/13-arrays.md', "المصفوفة مثل قائمة.\nنكتب الصَّفِيف هنا.\nالكودات\nوالكود\n");
        self::assertSame([
            'book/ar/13-arrays.md:2: "صفيف" is rejected; use مصفوفة (array)',
            'book/ar/13-arrays.md:4: "كود" is rejected; use شيفرة (code)',
        ], Glossary::check($this->root));
    }

    public function testShelfEqualToItsNewestSnapshotPasses(): void
    {
        $this->put('code/06-variables/shelf/shelf.php', 'old');
        $this->put('code/08-strings/shelf/shelf.php', 'new');
        $this->put('shelf/shelf.php', 'new');
        $this->put('shelf/README.md', 'about');
        $this->put('book/en/08-strings.md', "<!-- include: code/08-strings/shelf/shelf.php -->\n");
        self::assertSame([], Shelf::check($this->root));
    }

    public function testShelfThatDiffersFromItsNewestSnapshotFails(): void
    {
        $this->put('code/06-variables/shelf/shelf.php', 'old');
        $this->put('code/08-strings/shelf/shelf.php', 'new');
        $this->put('shelf/shelf.php', 'old');
        self::assertSame(['shelf/shelf.php differs from its newest snapshot code/08-strings/shelf/shelf.php'], Shelf::check($this->root));
    }

    public function testAFileRemovedByANewerSnapshotMustBeGone(): void
    {
        $this->put('code/47-forms/shelf/public/add.php', 'page');
        $this->put('code/48-front/shelf/public/add.php.removed', '');
        $this->put('shelf/public/add.php', 'page');
        self::assertSame(['shelf/public/add.php was removed in code/48-front/shelf/public/add.php.removed; delete it'], Shelf::check($this->root));
    }

    public function testAFileOfTheNewestStateMissingInShelfFails(): void
    {
        $this->put('code/47-forms/shelf/public/add.php', 'page');
        $this->put('code/48-front/shelf/public/index.php', 'front');
        $this->put('shelf/public/index.php', 'front');
        self::assertSame(['shelf/public/add.php is missing; its newest snapshot is code/47-forms/shelf/public/add.php'], Shelf::check($this->root));

        $this->put('code/48-front/shelf/public/add.php.removed', '');
        self::assertSame([], Shelf::check($this->root));
    }

    public function testShelfFileWithoutSnapshotFails(): void
    {
        $this->put('shelf/shelf.php', 'new');
        self::assertCount(1, Shelf::check($this->root));
    }

    public function testChapterThatIncludesTheLiveShelfFails(): void
    {
        $this->put('code/06-variables/shelf/shelf.php', 'x');
        $this->put('shelf/shelf.php', 'x');
        $this->put('book/en/06-variables.md', "<!-- include: shelf/shelf.php -->\n");
        self::assertSame(["book/en/06-variables.md: include the chapter's snapshot code/NN-slug/shelf/shelf.php, not shelf/shelf.php"], Shelf::check($this->root));
    }

    public function testOutputWithRunsTheScriptWithOtherStartingValues(): void
    {
        $this->put('a.php', "<?php\n\n\$copies = 2;\n\$title = 'A';\necho \$title, \$copies;\n");
        self::assertSame('B$0', output_with($this->root . '/a.php', ['copies' => '0', 'title' => "'B\$'"]));
        self::assertSame("<?php\n\n\$copies = 2;\n\$title = 'A';\necho \$title, \$copies;\n", file_get_contents($this->root . '/a.php'));
    }

    public function testOutputWithNeedsTheStartingLine(): void
    {
        $this->put('a.php', "<?php\n\necho 1;\n");
        $this->expectExceptionMessage('Keep the line $copies = ...;');
        output_with($this->root . '/a.php', ['copies' => '0']);
    }

    public function testOutputWithReplacesAnAssignmentOverSeveralLines(): void
    {
        $this->put('a.php', "<?php\n\n\$books = [\n    'A',\n    'B',\n];\n\necho count(\$books);\n");
        self::assertSame('1', output_with($this->root . '/a.php', ['books' => "['C']"]));
    }

    public function testOutputWithKeepsTheLinesAfterACommentedAssignment(): void
    {
        $this->put('a.php', "<?php\n\n\$age = 5;   // years\n\$x = 1;\necho \$age, \$x;\n");
        self::assertSame('91', output_with($this->root . '/a.php', ['age' => '9']));
    }
}
