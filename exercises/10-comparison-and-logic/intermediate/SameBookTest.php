<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SameBookTest extends TestCase
{
    public function testDifferentValuesAreNotEqual(): void
    {
        self::assertSame("bool(false)\nbool(false)\n", output_of(subject(__DIR__, 'same-book.php')));
    }

    public function testTheValuesStayAsTheyAre(): void
    {
        $strings = strings_in(subject(__DIR__, 'same-book.php'));
        self::assertSame(['0451526341', '451526341', '10', '1e1'], $strings, 'Change only the comparisons, not the values.');
    }
}
