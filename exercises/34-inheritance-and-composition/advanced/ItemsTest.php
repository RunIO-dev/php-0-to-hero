<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ItemsTest extends TestCase
{
    public function testADvd(): void
    {
        require_once subject(__DIR__, 'items.php');

        $dvd = new Dvd('  Spirited Away ', 125);

        self::assertInstanceOf(Item::class, $dvd);
        self::assertSame('Spirited Away', $dvd->title);
        self::assertSame(125, $dvd->minutes);
        self::assertSame(7, $dvd->loanDays());
        self::assertSame('"Spirited Away" (DVD)', $dvd->label());
        self::assertTrue((new ReflectionClass(Dvd::class))->isFinal(), 'No class may extend Dvd.');
    }

    public function testOverrideIsMarked(): void
    {
        require_once subject(__DIR__, 'items.php');

        foreach (['loanDays', 'label'] as $method) {
            self::assertNotEmpty((new ReflectionMethod(Dvd::class, $method))->getAttributes(Override::class), "Mark $method() with #[Override].");
        }
    }

    public function testDueDates(): void
    {
        require_once subject(__DIR__, 'items.php');

        $items = [new Item('Kindred'), new Dvd('Spirited Away', 125)];

        self::assertSame(
            ['Kindred' => '2026-11-30', 'Spirited Away' => '2026-11-23'],
            dueDates($items, new DateTimeImmutable('2026-11-16')),
        );
    }
}
