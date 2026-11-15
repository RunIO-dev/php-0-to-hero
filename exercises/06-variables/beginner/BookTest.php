<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = subject(__DIR__, 'book.php');
    }

    public function testPrintsTheBookOnThreeLines(): void
    {
        self::assertSame("Kitchen\nBanana Yoshimoto\n1988\n", output_of($this->file));
    }

    public function testUsesTheThreeVariables(): void
    {
        foreach (['title', 'author', 'year'] as $name) {
            self::assertContains($name, variables_in($this->file), "Use a variable named \$$name.");
        }
    }

    public function testWritesTheTitleOnlyOnce(): void
    {
        self::assertCount(1, array_keys(strings_in($this->file), 'Kitchen'), 'Write the title once, in $title, and print the variable.');
    }
}
