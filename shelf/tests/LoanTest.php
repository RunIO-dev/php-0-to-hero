<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\Loan;
use Shelf\Member;
use Shelf\Role;

final class LoanTest extends TestCase
{
    public function testAReaderHasTheBookForFourteenDays(): void
    {
        $loan = Loan::start(self::book(), new Member('Amira', Role::Reader), new DateTimeImmutable('2026-03-02'));

        self::assertSame('2026-03-16', $loan->due->format('Y-m-d'));
    }

    public function testStaffHaveItForTwentyEightDays(): void
    {
        $loan = Loan::start(self::book(), new Member('Jonas', Role::Staff), new DateTimeImmutable('2026-03-02'));

        self::assertSame('2026-03-30', $loan->due->format('Y-m-d'));
    }

    public function testTheDueDateCanBeInTheNextMonthAndYear(): void
    {
        $loan = Loan::start(self::book(), new Member('Amira', Role::Reader), new DateTimeImmutable('2026-12-24'));

        self::assertSame('2027-01-07', $loan->due->format('Y-m-d'));
    }

    public function testTheLineSaysWhoHasWhatUntilWhen(): void
    {
        $loan = Loan::start(self::book(), new Member('Amira', Role::Reader), new DateTimeImmutable('2026-03-02'));

        self::assertSame('Lent: Kindred to Amira, due back on Monday, 16 March 2026', $loan->line());
    }

    private static function book(): Book
    {
        return new Book('Kindred', 'Octavia E. Butler', 1979);
    }
}
