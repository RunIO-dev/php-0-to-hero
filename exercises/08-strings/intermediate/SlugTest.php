<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SlugTest extends TestCase
{
    private string $file;

    protected function setUp(): void
    {
        $this->file = subject(__DIR__, 'slug.php');
    }

    public function testPrintsTheSlug(): void
    {
        self::assertSame("the-left-hand-of-darkness\n", output_of($this->file));
    }

    public function testBuildsItFromTheTitle(): void
    {
        self::assertNotContains('the-left-hand-of-darkness', strings_in($this->file), 'Build the slug from $title with string functions.');
    }
}
