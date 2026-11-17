<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ComposerJsonTest extends TestCase
{
    /** @return array<mixed> */
    private static function project(): array
    {
        $json = (string) file_get_contents(subject(__DIR__, 'composer.json'));
        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($data, 'composer.json must hold an object');

        return $data;
    }

    public function testNameAndLicence(): void
    {
        $project = self::project();

        self::assertSame('learner/book-club', $project['name'] ?? null, 'Keep the name.');
        self::assertSame('MIT', $project['license'] ?? null);
    }

    public function testWhatItNeedsToRun(): void
    {
        self::assertSame(['php' => '>=8.5', 'ext-mbstring' => '*'], self::project()['require'] ?? null);
    }

    public function testTheAutoloader(): void
    {
        self::assertSame(['psr-4' => ['BookClub\\' => 'src/']], self::project()['autoload'] ?? null);
    }
}
