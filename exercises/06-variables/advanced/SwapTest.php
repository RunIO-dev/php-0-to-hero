<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SwapTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = subject(__DIR__, 'swap.php');
    }

    public function testTheBooksAreSwapped(): void
    {
        self::assertSame("Left:  Dracula\nRight: Frankenstein\n", output_of($this->file));
    }

    public function testEachTitleIsWrittenOnlyOnce(): void
    {
        $strings = strings_in($this->file);
        foreach (['Frankenstein', 'Dracula'] as $title) {
            self::assertCount(1, array_keys($strings, $title), "Write '$title' only once: move the value with variables.");
        }
    }
}
