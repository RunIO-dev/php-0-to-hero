<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Tools\Run;

final class BeginnerTest extends TestCase
{
    public function testYourAnswerIsWhatTheCommandPrints(): void
    {
        $answer = trim((string) file_get_contents(subject(__DIR__, 'answer.txt')));
        $printed = Run::capture([PHP_BINARY, '-r', 'echo 6 * 7;'])['out'];

        self::assertSame($printed, $answer, 'Run the command from README.md and copy what it printed.');
    }
}
