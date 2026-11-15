<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ListTest extends TestCase
{
    public function testTheCommandListsTheFolder(): void
    {
        $answer = trim((string) file_get_contents(subject(__DIR__, 'answer.txt')), " \n\r\t$");

        self::assertContains($answer, ['ls', 'dir', 'Get-ChildItem'], 'One short command; see the table in chapter 4.');
    }
}
