<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AmiraTest extends TestCase
{
    public function testYourFixIsTheOneTheCheckPrints(): void
    {
        $answer = (string) file_get_contents(subject(__DIR__, 'answer.txt'));
        // spaces and a leading "$ " don't matter
        $answer = (string) preg_replace('/\s+/', ' ', trim((string) preg_replace('/^\$\s*/', '', trim($answer))));

        self::assertSame('docker compose run --rm php php tools/check.php', $answer, 'The FIX under the PHP version names it.');
    }
}
