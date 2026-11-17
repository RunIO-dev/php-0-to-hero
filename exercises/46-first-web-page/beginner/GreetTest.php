<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GreetTest extends TestCase
{
    public function testGreetings(): void
    {
        [$amira, $nobody] = responses_of(dirname(subject(__DIR__, 'greet.php')), "GET /greet.php?name=Amira\n###\nGET /greet.php\n");

        self::assertStringContainsString('<h1>Hello, Amira!</h1>', $amira['body']);
        self::assertStringContainsString('<h1>Hello, reader!</h1>', $nobody['body']);
    }

    public function testHtmlInTheNameIsShownNotRun(): void
    {
        [$attack] = responses_of(dirname(subject(__DIR__, 'greet.php')), "GET /greet.php?name=%3Cscript%3Ealert(1)%3C%2Fscript%3E\n");

        self::assertStringNotContainsString('<script>', $attack['body']);
        self::assertStringContainsString('Hello, &lt;script&gt;alert(1)&lt;/script&gt;!', $attack['body']);
    }
}
