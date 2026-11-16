<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class NamesTest extends TestCase
{
    public function testCountsAndCutsCharactersNotBytes(): void
    {
        $expected = "Günter Grass: 12 characters, starts with G\nنجيب محفوظ: 10 characters, starts with ن\n";
        self::assertSame($expected, output_of(subject(__DIR__, 'names.php')));
    }
}
