<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class GreetingTest extends TestCase
{
    public function testBothLanguages(): void
    {
        require_once subject(__DIR__, 'greeting.php');

        self::assertInstanceOf(Greeting::class, new EnglishGreeting());
        self::assertInstanceOf(Greeting::class, new GermanGreeting());
        self::assertSame(['Hello, Amira.', 'Hello, Kenji.'], greetAll(new EnglishGreeting(), ['Amira', 'Kenji']));
        self::assertSame(['Hallo, Jonas.'], greetAll(new GermanGreeting(), ['Jonas']));
    }

    public function testAnyGreeting(): void
    {
        require_once subject(__DIR__, 'greeting.php');

        $spanish = new class implements Greeting {
            public function text(string $name): string
            {
                return "Hola, $name.";
            }
        };

        self::assertSame(['Hola, Ana.'], greetAll($spanish, ['Ana']));
    }
}
