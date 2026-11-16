<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class TypesTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = subject(__DIR__, 'types.php');
    }

    public function testShowsTheFiveValuesWithTheirTypes(): void
    {
        $expected = "string(7) \"Beloved\"\nint(324)\nfloat(4.5)\nbool(false)\nNULL\n";
        self::assertSame($expected, output_of($this->file));
    }

    public function testUsesTheFiveVariables(): void
    {
        foreach (['title', 'pages', 'rating', 'isAvailable', 'returnedAt'] as $name) {
            self::assertContains($name, variables_in($this->file), "Use a variable named \$$name.");
        }
    }
}
