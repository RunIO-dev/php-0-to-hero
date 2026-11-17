<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class LoanTest extends TestCase
{
    public function testTheDueDate(): void
    {
        require_once subject(__DIR__, 'loan.php');

        $book = new Book('Kindred');
        $member = new Member('Amira');
        $loan = new Loan($book, $member, new DateTimeImmutable('2026-11-16'));

        self::assertSame($book, $loan->book);
        self::assertSame($member, $loan->member);
        self::assertSame('2026-11-30', $loan->due->format('Y-m-d'));
        self::assertSame('2026-12-16', (new Loan($book, $member, new DateTimeImmutable('2026-11-16'), 30))->due->format('Y-m-d'));
    }

    public function testDaysLate(): void
    {
        require_once subject(__DIR__, 'loan.php');

        $loan = new Loan(new Book('Kitchen'), new Member('Kenji'), new DateTimeImmutable('2026-11-16'));

        self::assertSame(0, $loan->daysLate(new DateTimeImmutable('2026-11-20')));
        self::assertSame(0, $loan->daysLate(new DateTimeImmutable('2026-11-30')));
        self::assertSame(1, $loan->daysLate(new DateTimeImmutable('2026-12-01')));
        self::assertSame(33, $loan->daysLate(new DateTimeImmutable('2027-01-02')));
    }
}
