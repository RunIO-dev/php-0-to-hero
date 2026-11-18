<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'reminder.php');

final class ReminderTest extends TestCase
{
    /** @return iterable<string, array{string, ?string, ?string}> today, the last reminder, what is due; due on 2026-03-16 */
    public static function days(): iterable
    {
        yield 'a week before' => ['2026-03-09', null, null];
        yield 'two days before' => ['2026-03-14', null, 'due soon'];
        yield 'the next day' => ['2026-03-15', '2026-03-14', null];
        yield 'an older reminder' => ['2026-03-15', '2026-03-01', 'due soon'];
        yield 'on the due date, none yet' => ['2026-03-16', null, 'due soon'];
        yield 'the day after' => ['2026-03-17', '2026-03-14', 'overdue'];
        yield 'overdue, never reminded' => ['2026-03-20', null, 'overdue'];
        yield 'reminded yesterday' => ['2026-03-18', '2026-03-17', null];
        yield 'six days later' => ['2026-03-23', '2026-03-17', null];
        yield 'a week later' => ['2026-03-24', '2026-03-17', 'overdue'];
    }

    #[DataProvider('days')]
    public function testTheRightReminderOnTheRightDay(string $today, ?string $last, ?string $reminder): void
    {
        self::assertSame($reminder, reminderFor('2026-03-16', $today, $last));
    }

    public function testAcrossAMonth(): void
    {
        self::assertSame('due soon', reminderFor('2026-04-01', '2026-03-30', null));
        self::assertNull(reminderFor('2026-04-01', '2026-03-29', null));
    }
}
