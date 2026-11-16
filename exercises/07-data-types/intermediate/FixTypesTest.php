<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FixTypesTest extends TestCase
{
    public function testEveryValueHasTheRightType(): void
    {
        $expected = "string(22) \"The Remains of the Day\"\nint(1989)\nfloat(4.25)\nbool(true)\nNULL\n";
        self::assertSame($expected, output_of(subject(__DIR__, 'fix-types.php')));
    }
}
