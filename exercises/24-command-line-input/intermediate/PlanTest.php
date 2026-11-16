<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PlanTest extends TestCase
{
    private const REFUSED = "Pages and pages per day must be whole numbers from 1 to 10000.\n";

    /** @return array<string, array{list<string>, string}> */
    public static function inputs(): array
    {
        return [
            'a plan' => [['320', '30'], "320 pages at 30 a day: 11 days\n"],
            'exact' => [['300', '30'], "300 pages at 30 a day: 10 days\n"],
            'missing' => [['320'], self::REFUSED],
            'nothing' => [[], self::REFUSED],
            'a word' => [['320', 'lots'], self::REFUSED],
            'zero' => [['320', '0'], self::REFUSED],
            'negative' => [['-5', '30'], self::REFUSED],
            'a fraction' => [['320', '7.5'], self::REFUSED],
            'too many' => [['20000', '30'], self::REFUSED],
            'text after the number' => [['320 pages', '30'], self::REFUSED],
        ];
    }

    /** @param list<string> $args */
    #[DataProvider('inputs')]
    public function testPlan(array $args, string $expected): void
    {
        self::assertSame($expected, output_of(subject(__DIR__, 'plan.php'), $args));
    }
}
