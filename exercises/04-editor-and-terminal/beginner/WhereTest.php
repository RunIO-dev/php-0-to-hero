<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class WhereTest extends TestCase
{
    public function testTheCommandShowsTheCurrentFolder(): void
    {
        $answer = trim((string) file_get_contents(subject(__DIR__, 'answer.txt')), " \n\r\t$");

        self::assertContains($answer, ['pwd', 'cd', 'Get-Location'], 'One short command; see the table in chapter 4.');
    }
}
