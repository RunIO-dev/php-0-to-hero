<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'DueLabel.php');

final class DueLabelTest extends TestCase
{
    /** @return iterable<string, array{string, string, string, string}> today, due, label, level */
    public static function dates(): iterable
    {
        yield 'three weeks' => ['2026-11-10 09:00', '2026-12-01', 'due in 21 days', 'ok'];
        yield 'three days' => ['2026-11-28 23:59', '2026-12-01', 'due in 3 days', 'ok'];
        yield 'two days' => ['2026-11-29 00:00', '2026-12-01', 'due in 2 days', 'warn'];
        yield 'tomorrow' => ['2026-11-30 18:00', '2026-12-01', 'due tomorrow', 'warn'];
        yield 'today, late at night' => ['2026-12-01 23:59', '2026-12-01', 'due today', 'warn'];
        yield 'a day late, just after midnight' => ['2026-12-02 00:01', '2026-12-01', '1 day overdue', 'danger'];
        yield 'two weeks late' => ['2026-12-15 08:00', '2026-12-01', '14 days overdue', 'danger'];
        yield 'across a new year' => ['2026-12-30 12:00', '2027-01-02', 'due in 3 days', 'ok'];
        yield 'the due date with a time' => ['2026-11-30 08:00', '2026-12-01 17:30', 'due tomorrow', 'warn'];
    }

    #[DataProvider('dates')]
    public function testADueDateReadsAsOfToday(string $today, string $due, string $label, string $level): void
    {
        $labels = new DueLabel(new DateTimeImmutable($today));

        self::assertSame($label, $labels->label(new DateTimeImmutable($due)));
        self::assertSame($level, $labels->level(new DateTimeImmutable($due)));
    }

    public function testSummerTimeChangesNothing(): void
    {
        $berlin = new DateTimeZone('Europe/Berlin');
        // The clocks go back one hour in the night to 25 October 2026: that day has 25 hours.
        $labels = new DueLabel(new DateTimeImmutable('2026-10-24 00:30', $berlin));

        self::assertSame('due tomorrow', $labels->label(new DateTimeImmutable('2026-10-25', $berlin)));
        self::assertSame('due in 2 days', $labels->label(new DateTimeImmutable('2026-10-26', $berlin)));
        // And forward in March: 29 March 2026 has 23 hours.
        $labels = new DueLabel(new DateTimeImmutable('2026-03-28 23:30', $berlin));
        self::assertSame('due in 2 days', $labels->label(new DateTimeImmutable('2026-03-30', $berlin)));
    }
}
