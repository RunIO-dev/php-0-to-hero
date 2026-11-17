<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Exercise 41, intermediate: these tests are written first. Read them, then
 * write dueDate() in due-date.php so that they pass.
 */
final class DueDateTest extends TestCase
{
    public function testTheBookIsDueAfterTheLoanDays(): void
    {
        require_once subject(__DIR__, 'due-date.php');

        self::assertSame('2026-03-16', dueDate(new DateTimeImmutable('2026-03-02'), 14)->format('Y-m-d'));
    }

    public function testTheLibraryIsClosedOnSundaysSoTheBookIsDueOnMonday(): void
    {
        require_once subject(__DIR__, 'due-date.php');

        // 2026-03-01 + 14 days is Sunday, 15 March
        self::assertSame('2026-03-16', dueDate(new DateTimeImmutable('2026-03-01'), 14)->format('Y-m-d'));
    }

    public function testTheStartDateDoesNotChange(): void
    {
        require_once subject(__DIR__, 'due-date.php');

        $start = new DateTimeImmutable('2026-03-01');
        dueDate($start, 14);
        self::assertSame('2026-03-01', $start->format('Y-m-d'));
    }
}
