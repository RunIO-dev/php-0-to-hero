<?php

declare(strict_types=1);

namespace Shelf\Tests\Database;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\Hold;
use Shelf\Loan;
use Shelf\Member;
use Shelf\Report\PastLoan;
use Shelf\Report\Statistics;
use Shelf\Report\Tally;
use Shelf\Role;

/**
 * The dashboard's numbers (chapter 86), from a real SQLite database: a report is its SQL, so the test
 * runs the SQL. Today is 20 March 2026. Kindred was lent three times, Sula once, Beloved never.
 */
final class StatisticsTest extends TestCase
{
    private EntityManager $entities;
    private Statistics $statistics;
    private DateTimeImmutable $today;

    protected function setUp(): void
    {
        $this->entities = Entities::manager();
        $this->statistics = new Statistics($this->entities->getConnection());
        $this->today = new DateTimeImmutable('2026-03-20');

        $kindred = new Book('Kindred', 'Octavia E. Butler', 1979, 2);
        $sula = new Book('Sula', 'Toni Morrison', 1973, 1);
        $beloved = new Book('Beloved', 'Toni Morrison', 1987, 1);
        $amira = new Member('Amira', Role::Reader);
        $ben = new Member('Ben', Role::Reader);
        foreach ([$kindred, $sula, $beloved, $amira, $ben] as $entity) {
            $this->entities->persist($entity);
        }
        // Kindred: in January, back on time; in February, back two days late; now, overdue since 16 March.
        $this->loan($kindred, $amira, '2026-01-05', back: '2026-01-15');
        $this->loan($kindred, $ben, '2026-02-02', back: '2026-02-18');
        $this->loan($kindred, $amira, '2026-03-02');
        // Sula: lent on 10 March, due 24 March.
        $this->loan($sula, $ben, '2026-03-10');
        $waiting = new Hold($sula, $amira, new DateTimeImmutable('2026-03-11'));
        $this->entities->persist($waiting);
        $this->entities->flush();
    }

    private function loan(Book $book, Member $member, string $lent, ?string $back = null): void
    {
        $loan = Loan::start($book, $member, new DateTimeImmutable($lent));
        if ($back !== null) {
            $loan->giveBack(new DateTimeImmutable($back));
        }
        $this->entities->persist($loan);
    }

    public function testTheOverviewCountsInOneQuery(): void
    {
        $overview = $this->statistics->overview($this->today);

        self::assertSame([3, 4, 2, 1, 1, 0, 2], [
            $overview->titles, $overview->onShelf, $overview->onLoan, $overview->overdue,
            $overview->waiting, $overview->setAside, $overview->members,
        ]);
    }

    public function testTheBooksBorrowedMostComeFirst(): void
    {
        self::assertEquals(
            [new Tally('Kindred', 3), new Tally('Sula', 1)],
            $this->statistics->mostBorrowed(new DateTimeImmutable('2026-01-01')),
        );
        self::assertEquals([new Tally('Kindred', 2)], $this->statistics->mostBorrowed(new DateTimeImmutable('2026-02-01'), limit: 1));
    }

    public function testEveryMonthHasALineEvenWithoutLoans(): void
    {
        $months = $this->statistics->perMonth($this->today, 4);

        self::assertEquals([new Tally('2025-12', 0), new Tally('2026-01', 1), new Tally('2026-02', 1), new Tally('2026-03', 2)], $months);
    }

    public function testReturnsCountTheLateOnes(): void
    {
        $returns = $this->statistics->returns(new DateTimeImmutable('2026-01-01'));

        self::assertSame([2, 1, 50], [$returns->returned, $returns->late, $returns->onTimePercent()]);
        self::assertNull($this->statistics->returns($this->today)->onTimePercent(), 'Nothing came back: no percent at all');
    }

    public function testABookNobodyBorrowedCountsZeroNotOne(): void
    {
        self::assertSame(['Beloved'], $this->statistics->unborrowed(new DateTimeImmutable('2025-03-20')));
        self::assertSame(['Beloved', 'Kindred'], $this->statistics->unborrowed(new DateTimeImmutable('2026-03-05')), 'Sula was lent on 10 March');
    }

    public function testTheHistoryIsNewestFirstAndNarrows(): void
    {
        $all = $this->statistics->history(null, null, 0, 10);
        self::assertSame(['Sula', 'Kindred', 'Kindred', 'Kindred'], array_map(fn(PastLoan $loan): string => $loan->title, $all));
        self::assertSame(4, $this->statistics->historyCount(null, null));

        $page = $this->statistics->history(null, null, 1, 2);
        self::assertSame(['2026-03-02', '2026-02-02'], array_map(fn(PastLoan $loan): string => $loan->lentOn->format('Y-m-d'), $page));

        self::assertSame(2, $this->statistics->historyCount('kindred', 'AMIRA'), 'Titles and names in any case');
        self::assertSame(0, $this->statistics->historyCount("Kindred' OR '1'='1", null), 'A value, never SQL');
    }

    public function testHowLateALoanIs(): void
    {
        [$sula, $overdue, $late, $onTime] = $this->statistics->history(null, null, 0, 10);

        self::assertSame([0, 4, 2, 0], [$sula->daysLate($this->today), $overdue->daysLate($this->today), $late->daysLate($this->today), $onTime->daysLate($this->today)]);
        self::assertNull($overdue->returnedOn);
    }
}
