<?php

declare(strict_types=1);

namespace Shelf\Tests\Twig;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shelf\Twig\ShelfExtension;
use Symfony\Component\Clock\MockClock;

/** Shelf's Twig filters (chapter 80), with a clock that stands still. */
final class ShelfExtensionTest extends TestCase
{
    /** @return iterable<string, array{string, string, string}> today, then the label and level of a loan due on 1 December */
    public static function days(): iterable
    {
        yield 'a week before' => ['2026-11-24 09:30', 'due in 7 days', 'ok'];
        yield 'three days before' => ['2026-11-28 23:59', 'due in 3 days', 'ok'];
        yield 'two days before' => ['2026-11-29 00:00', 'due in 2 days', 'warn'];
        yield 'the day before' => ['2026-11-30 18:00', 'due tomorrow', 'warn'];
        yield 'the day' => ['2026-12-01 23:00', 'due today', 'warn'];
        yield 'one day late' => ['2026-12-02 08:00', '1 day overdue', 'danger'];
        yield 'two weeks late' => ['2026-12-15 08:00', '14 days overdue', 'danger'];
    }

    #[DataProvider('days')]
    public function testADueDateReadsAsOfToday(string $today, string $label, string $level): void
    {
        $extension = new ShelfExtension(new MockClock($today));
        $due = new DateTimeImmutable('2026-12-01');

        self::assertSame($label, $extension->due($due));
        self::assertSame($level, $extension->dueLevel($due));
    }

    public function testATitleAlwaysGetsTheSameTone(): void
    {
        $extension = new ShelfExtension(new MockClock());

        self::assertSame($extension->tone('Kindred'), $extension->tone('Kindred'));
        foreach (['Kindred', 'Sula', 'Kitchen', 'Beloved', ''] as $title) {
            self::assertContains($extension->tone($title), range(0, 5));
        }
    }
}
