<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class BrokenTest extends TestCase
{
    public function testPrintsTheTwoLines(): void
    {
        self::assertSame("Welcome to Shelf.\nOpening hours: 9 to 18.\n", output_of(subject(__DIR__, 'broken.error.php')));
    }
}
