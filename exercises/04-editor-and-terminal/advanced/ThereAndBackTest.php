<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ThereAndBackTest extends TestCase
{
    /** @return list<string> the commands, one per line, with \ written as / */
    private function commands(): array
    {
        $text = str_replace('\\', '/', (string) file_get_contents(subject(__DIR__, 'answer.txt')));
        $lines = array_map(static fn(string $l): string => (string) preg_replace('/\s+/', ' ', trim($l, " \t$")), explode("\n", $text));

        return array_values(array_filter($lines, static fn(string $l): bool => $l !== ''));
    }

    public function testTwoCommands(): void
    {
        self::assertCount(2, $this->commands(), 'Write two commands, one per line.');
    }

    public function testFirstGoesIntoTheFolder(): void
    {
        self::assertSame('cd exercises/04-editor-and-terminal', rtrim($this->commands()[0] ?? '', '/'));
    }

    public function testSecondGoesBackUpTwoFolders(): void
    {
        self::assertSame('cd ../..', rtrim($this->commands()[1] ?? '', '/'), 'The folder is two levels down, so go up two.');
    }
}
