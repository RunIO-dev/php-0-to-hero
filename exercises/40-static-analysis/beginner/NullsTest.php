<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class NullsTest extends TestCase
{
    public function testPhpstanAtLevel8FindsNothing(): void
    {
        $analysis = analysis_of(subject(__DIR__, 'nulls.php'), 8);

        self::assertTrue($analysis['passed'], "PHPStan says:\n" . $analysis['out']);
        self::assertStringNotContainsString('(string)', (string) file_get_contents(subject(__DIR__, 'nulls.php')), 'Check for null instead of a cast.');
    }

    public function testTheGreetings(): void
    {
        self::assertSame("Kindred: with AMIRA\nSula: with JONAS\n", output_of(subject(__DIR__, 'nulls.php')));

        ob_start(); // the script prints its greetings when it is loaded
        require_once subject(__DIR__, 'nulls.php');
        ob_end_clean();
        self::assertSame('Beloved: on the shelf', greeting('Beloved'));
    }
}
