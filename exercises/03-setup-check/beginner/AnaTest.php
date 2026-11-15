<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AnaTest extends TestCase
{
    public function testYourFixIsTheOneTheCheckPrints(): void
    {
        $answer = (string) file_get_contents(subject(__DIR__, 'answer.txt'));
        // spaces and a leading "$ " don't matter
        $answer = (string) preg_replace('/\s+/', ' ', trim((string) preg_replace('/^\$\s*/', '', trim($answer))));

        self::assertSame('sudo apt install php-sqlite3', $answer, 'Look at the FIX line for "Debian or Ubuntu".');
    }
}
