<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GreetTest extends TestCase
{
    public function testGreetsTheName(): void
    {
        self::assertSame("Welcome to the library, Amira!\n", output_of(subject(__DIR__, 'greet.php'), ['Amira']));
        self::assertSame("Welcome to the library, Ana Lima!\n", output_of(subject(__DIR__, 'greet.php'), ['Ana Lima']));
    }

    public function testExplainsTheUsage(): void
    {
        self::assertSame("Usage: php greet.php <name>\n", output_of(subject(__DIR__, 'greet.php')));
        self::assertSame("Usage: php greet.php <name>\n", output_of(subject(__DIR__, 'greet.php'), ['Amira', 'Jonas']));
    }
}
