<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AverageTest extends TestCase
{
    /** @return array<string, array{list<int|float>, float}> */
    public static function ratings(): array
    {
        return [
            'whole' => [[4, 5, 3], 4.0],
            'a half' => [[4, 5], 4.5],
            'one rating' => [[3], 3.0],
            'floats' => [[2.5, 3.5], 3.0],
            'none' => [[], 0.0],
        ];
    }

    /** @param list<int|float> $ratings */
    #[DataProvider('ratings')]
    public function testAverage(array $ratings, float $expected): void
    {
        require_once subject(__DIR__, 'average.php');

        self::assertSame($expected, average($ratings));
    }

    public function testSaysItsTypes(): void
    {
        require_once subject(__DIR__, 'average.php');
        $function = new ReflectionFunction('average');

        self::assertSame('float', (string) $function->getReturnType(), 'add the return type');
        self::assertSame('array', (string) $function->getParameters()[0]->getType(), 'add the parameter type');
        self::assertStringContainsString('@param list<', (string) $function->getDocComment(), 'describe the list in a PHPDoc comment');
    }
}
