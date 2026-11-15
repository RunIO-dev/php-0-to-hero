<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AdvancedTest extends TestCase
{
    public function testTheOptionChecksSyntaxWithoutRunning(): void
    {
        $answer = trim((string) file_get_contents(subject(__DIR__, 'answer.txt')));

        self::assertContains($answer, ['-l', '--syntax-check'], 'Look in php -h for "syntax check".');
    }
}
