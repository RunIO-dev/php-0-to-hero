<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RenameTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = subject(__DIR__, 'rename.php');
    }

    public function testOutputStaysTheSame(): void
    {
        self::assertSame("The Little Prince\nAntoine de Saint-Exupéry\n1943\n4 copies\n", output_of($this->file));
    }

    public function testEveryNameSaysWhatItHolds(): void
    {
        foreach (variables_in($this->file) as $name) {
            self::assertGreaterThanOrEqual(4, strlen($name), "\$$name is too short to say what it holds.");
        }
    }
}
