<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StatusTest extends TestCase
{
    /** @return array<string, array{string, bool, string}> */
    public static function days(): array
    {
        return [
            'long before' => ['2026-11-20', false, 'OnTime'],
            'four days before' => ['2026-11-26', false, 'OnTime'],
            'three days before' => ['2026-11-27', false, 'DueSoon'],
            'on the due date' => ['2026-11-30', false, 'DueSoon'],
            'one day late' => ['2026-12-01', false, 'Overdue'],
            'returned late' => ['2026-12-05', true, 'Returned'],
            'returned early' => ['2026-11-20', true, 'Returned'],
        ];
    }

    #[DataProvider('days')]
    public function testStatusOf(string $today, bool $returned, string $expected): void
    {
        require_once subject(__DIR__, 'status.php');

        $status = statusOf(new DateTimeImmutable('2026-11-30'), new DateTimeImmutable($today), $returned);

        self::assertSame($expected, $status->name);
    }

    public function testLabelsAndReminders(): void
    {
        require_once subject(__DIR__, 'status.php');

        self::assertSame(['returned', 'on loan', 'due soon', 'overdue'], array_map(fn(LoanStatus $s): string => $s->label(), LoanStatus::cases()));
        self::assertSame([false, false, true, true], array_map(fn(LoanStatus $s): bool => $s->needsReminder(), LoanStatus::cases()));
    }
}
