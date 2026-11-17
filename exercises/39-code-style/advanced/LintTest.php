<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LintTest extends TestCase
{
    public function testTidyCodeHasNoProblems(): void
    {
        require_once subject(__DIR__, 'lint.php');

        self::assertSame([], styleProblems("<?php\n\necho 'Kindred';\n"));
        self::assertSame([], styleProblems(''));
    }

    public function testEachProblem(): void
    {
        require_once subject(__DIR__, 'lint.php');

        self::assertSame(['line 2: tab used to indent'], styleProblems("<?php\n\techo 1;\n"));
        self::assertSame(['line 2: tab used to indent'], styleProblems("<?php\n  \techo 1;\n"));
        self::assertSame(['line 2: spaces at the end'], styleProblems("<?php\necho 1; \n"));
        self::assertSame(['line 1: longer than 120 characters'], styleProblems(str_repeat('a', 121) . "\n"));
        self::assertSame(['no new line at the end'], styleProblems("<?php\necho 1;"));
    }

    public function testCountsCharactersNotBytes(): void
    {
        require_once subject(__DIR__, 'lint.php');

        self::assertSame([], styleProblems("echo '" . str_repeat('ä', 110) . "';\n"));
        self::assertSame([], styleProblems("echo 'a\tb';\n"), 'A tab inside a line is not indentation.');
    }

    public function testSeveralProblemsInOrder(): void
    {
        require_once subject(__DIR__, 'lint.php');

        self::assertSame([
            'line 2: tab used to indent',
            'line 2: spaces at the end',
            'line 3: spaces at the end',
            'no new line at the end',
        ], styleProblems("<?php\n\techo 1; \necho 2;\t\necho 3;"));
    }
}
