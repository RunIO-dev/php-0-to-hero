<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class HelloTest extends TestCase
{
    public function testPrintsHelloPhpOnItsOwnLine(): void
    {
        self::assertSame("Hello, PHP!\n", output_of(subject(__DIR__, 'hello.php')));
    }
}
