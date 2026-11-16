<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CommandsTest extends TestCase
{
    public function testEveryCommandStillWorks(): void
    {
        require_once subject(__DIR__, 'commands.php');

        self::assertSame("Commands: help, hello <name>, count <words>\n", run([]));
        self::assertSame("Commands: help, hello <name>, count <words>\n", run(['help']));
        self::assertSame("Hello, Amira!\n", run(['hello', 'Amira']));
        self::assertSame("Hello, reader!\n", run(['hello']));
        self::assertSame("3 words\n", run(['count', 'a', 'b', 'c']));
    }

    public function testAnUnknownCommandThrows(): void
    {
        require_once subject(__DIR__, 'commands.php');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown command "fly"');
        run(['fly']);
    }

    public function testTheCommandsAreListedOnce(): void
    {
        $code = (string) file_get_contents(subject(__DIR__, 'commands.php'));
        $code = (string) preg_replace('~^//.*$~m', '', $code);

        self::assertSame(1, substr_count($code, "'hello'"), "List 'hello' only in the match.");
        self::assertStringNotContainsString('in_array', $code);
    }
}
