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

    /** Chapter 83: extended by the member's loan period, twice at most. */
    public function testALoanIsExtendedTwiceAtMost(): void
    {
        $loan = Loan::start(self::book(), new Member('Amira', Role::Reader), new DateTimeImmutable('2026-03-02'));
        $loan->renew(new DateTimeImmutable('2026-03-16 23:59'));
        $loan->renew(new DateTimeImmutable('2026-03-20'));

        self::assertSame(['2026-04-13', 2], [$loan->due->format('Y-m-d'), $loan->renewals]);
        $this->expectExceptionMessage('was extended 2 times already, which is the most.');
        $loan->renew(new DateTimeImmutable('2026-03-21'));
    }

    public function testAnOverdueLoanIsNotExtended(): void
    {
        $loan = Loan::start(self::book(), new Member('Amira', Role::Reader), new DateTimeImmutable('2026-03-02'));

        self::assertFalse($loan->isOverdue(new DateTimeImmutable('2026-03-16 23:59')));
        self::assertTrue($loan->isOverdue(new DateTimeImmutable('2026-03-17 00:00')));
        $this->expectExceptionMessage('is overdue: please bring it back to the library.');
        $loan->renew(new DateTimeImmutable('2026-03-17'));
    }

    public function testABookComesBackOnce(): void
    {
        $loan = Loan::start(self::book(), new Member('Amira', Role::Reader), new DateTimeImmutable('2026-03-02'));
        $loan->giveBack(new DateTimeImmutable('2026-03-20 10:00'));

        self::assertFalse($loan->isOverdue(new DateTimeImmutable('2026-04-01')), 'Back, so never overdue');
        $this->expectExceptionMessage('is back already.');
        $loan->giveBack(new DateTimeImmutable('2026-03-21'));
    }

    private static function book(): Book
    {
        return new Book('Kindred', 'Octavia E. Butler', 1979);
    }
}
