<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class NamesTest extends TestCase
{
    public function testTheNames(): void
    {
        require_once subject(__DIR__, 'names.php');

        self::assertTrue(class_exists('LateFeeCalculator'), 'The class is called LateFeeCalculator.');
        $class = new ReflectionClass('LateFeeCalculator');
        self::assertSame('LateFeeCalculator', $class->getName());
        self::assertSame(['CENTS_PER_DAY', 'MAX_FINE'], array_keys($class->getConstants()));
        $methods = array_map(fn(ReflectionMethod $m): string => $m->getName(), $class->getMethods());
        self::assertSame(['calculate', 'isFree'], $methods);
    }

    public function testItStillWorks(): void
    {
        require_once subject(__DIR__, 'names.php');
        self::assertTrue(class_exists('LateFeeCalculator'), 'The class is called LateFeeCalculator.');

        $calculator = new LateFeeCalculator();
        self::assertSame(60, $calculator->calculate(3));
        self::assertSame(500, $calculator->calculate(40));
        self::assertSame(0, $calculator->calculate(0));
        self::assertTrue($calculator->isFree(-1));
    }
}
