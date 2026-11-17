<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\BookRepository;
use Shelf\Isbn;
use Shelf\Loan;
use Shelf\LoanRepository;
use Shelf\Member;
use Shelf\NoCopyLeft;
use Shelf\Role;

/** What every LoanRepository must do (chapter 60), in memory and in the database. */
abstract class LoanRepositoryContract extends TestCase
{
    protected Member $amira;
    protected Member $jonas;

    protected function setUp(): void
    {
        $this->amira = new Member('Amira', Role::Reader);
        $this->jonas = new Member('Jonas', Role::Staff);
    }

    /**
     * New, empty repositories with these books, and the members Amira and Jonas.
     *
     * @return array{BookRepository, LoanRepository}
     */
    abstract protected function repositories(Book ...$books): array;

    private function loan(Book $book, Member $member, string $due): Loan
    {
        return new Loan($book, $member, new DateTimeImmutable($due));
    }

    public function testALoanTakesACopy(): void
    {
        $sula = new Book('Sula', 'Toni Morrison', 1973, 2);
        [$books, $loans] = $this->repositories($sula);

        $loans->add($this->loan($sula, $this->amira, '2026-03-16'));

        self::assertSame(1, $books->find('Sula')?->copies);
        self::assertTrue($loans->isOnLoan('sula'));
    }

    /** Chapter 77: a book with an ISBN, an object, which Doctrine reads again after the loan. */
    public function testABookWithAnIsbnIsLentToo(): void
    {
        $kindred = new Book('Kindred', 'Octavia E. Butler', 1979, 2, new Isbn('9780807083697'));
        [$books, $loans] = $this->repositories($kindred);

        $loans->add($this->loan($kindred, $this->amira, '2026-03-16'));

        self::assertSame(1, $books->find('Kindred')?->copies);
        self::assertSame('9780807083697', $kindred->isbn?->digits);
    }

    public function testTheLastCopyGoesOnce(): void
    {
        $kindred = new Book('Kindred', 'Octavia E. Butler', 1979, 1);
        [$books, $loans] = $this->repositories($kindred);
        $loans->add($this->loan($kindred, $this->amira, '2026-03-16'));

        try {
            $loans->add($this->loan($kindred, $this->jonas, '2026-03-30'));
            self::fail('The last copy was lent twice');
        } catch (NoCopyLeft) {
        }
        self::assertSame(0, $books->find('Kindred')?->copies);
        self::assertSame([], $loans->forMember('Jonas'));
    }

    public function testAMembersLoansTheSoonestDueFirst(): void
    {
        $kindred = new Book('Kindred', 'Octavia E. Butler', 1979);
        $sula = new Book('Sula', 'Toni Morrison', 1973);
        $kitchen = new Book('Kitchen', 'Banana Yoshimoto', 1988);
        [, $loans] = $this->repositories($kindred, $sula, $kitchen);
        $loans->add($this->loan($kindred, $this->amira, '2026-03-20'));
        $loans->add($this->loan($sula, $this->jonas, '2026-03-10'));
        $loans->add($this->loan($kitchen, $this->amira, '2026-03-16'));

        $titles = array_map(fn(Loan $loan): string => $loan->book->title, $loans->forMember('amira'));

        self::assertSame(['Kitchen', 'Kindred'], $titles);
    }

    public function testABookNobodyBorrowedIsNotOnLoan(): void
    {
        [, $loans] = $this->repositories(new Book('Kindred', 'Octavia E. Butler', 1979));

        self::assertFalse($loans->isOnLoan('Kindred'));
        self::assertSame([], $loans->forMember('Amira'));
    }
}
