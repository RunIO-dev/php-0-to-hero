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
        $back = new DateTimeImmutable($due);

        return new Loan($book, $member, $back, $back->modify('-14 days'));
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

    /** Chapter 83: a returned loan puts its copy back, and stays, as history. */
    public function testAReturnedBookIsOnTheShelfAgain(): void
    {
        $sula = new Book('Sula', 'Toni Morrison', 1973, 1);
        [$books, $loans] = $this->repositories($sula);
        $loans->add($loan = $this->loan($sula, $this->amira, '2026-03-16'));

        $loans->giveBack($loan, new DateTimeImmutable('2026-03-10 17:30'));

        self::assertSame(1, $books->find('Sula')?->copies);
        self::assertFalse($loans->isOnLoan('Sula'));
        self::assertSame([], $loans->forMember('Amira'));
        self::assertSame('2026-03-10', $loans->find($loan->id())?->returnedOn?->format('Y-m-d'), 'The loan is kept, with its day');
    }

    public function testTheCurrentLoansAreTheOnesNotBack(): void
    {
        $kindred = new Book('Kindred', 'Octavia E. Butler', 1979, 2);
        $sula = new Book('Sula', 'Toni Morrison', 1973);
        [, $loans] = $this->repositories($kindred, $sula);
        $loans->add($this->loan($kindred, $this->jonas, '2026-03-20'));
        $loans->add($back = $this->loan($sula, $this->amira, '2026-03-12'));
        $loans->add($this->loan($kindred, $this->amira, '2026-03-20'));
        $loans->giveBack($back, new DateTimeImmutable('2026-03-11'));

        $current = array_map(fn(Loan $loan): string => "{$loan->book->title}/{$loan->member->name}", $loans->current());

        self::assertSame(['Kindred/Amira', 'Kindred/Jonas'], $current);
        self::assertNull($loans->find(999));
    }

    public function testAnExtendedLoanIsStored(): void
    {
        $sula = new Book('Sula', 'Toni Morrison', 1973);
        [, $loans] = $this->repositories($sula);
        $loans->add($loan = $this->loan($sula, $this->amira, '2026-03-16'));

        $loan->renew(new DateTimeImmutable('2026-03-15'));
        $loans->update($loan);

        $stored = $loans->find($loan->id());
        self::assertSame(['2026-03-30', 1], [$stored?->due->format('Y-m-d'), $stored?->renewals]);
    }

    public function testAMembersHistoryCanBeForgotten(): void
    {
        $sula = new Book('Sula', 'Toni Morrison', 1973, 2);
        [, $loans] = $this->repositories($sula);
        $loans->add($back = $this->loan($sula, $this->amira, '2026-03-16'));
        $loans->add($out = $this->loan($sula, $this->amira, '2026-03-30'));
        $loans->giveBack($back, new DateTimeImmutable('2026-03-10'));
        $id = $back->id(); // Doctrine forgets the number of what it deletes

        $loans->forget($this->amira);

        self::assertNull($loans->find($id));
        self::assertNotNull($loans->find($out->id()), 'A loan that is out is never forgotten');
    }

    /** A book that was lent and came back can be removed: its history goes first (chapter 83). */
    public function testARemovedBooksHistoryGoesWithIt(): void
    {
        $sula = new Book('Sula', 'Toni Morrison', 1973, 2);
        [$books, $loans] = $this->repositories($sula);
        $loans->add($back = $this->loan($sula, $this->amira, '2026-03-16'));
        $loans->giveBack($back, new DateTimeImmutable('2026-03-10'));
        $id = $back->id();

        $loans->forgetBook($sula);
        $books->remove('Sula');

        self::assertNull($loans->find($id));
        self::assertNull($books->find('Sula'));
    }

    /** Chapter 85: a reminder is claimed once; a caller who read an older state gets false. */
    public function testAReminderIsClaimedOnce(): void
    {
        $sula = new Book('Sula', 'Toni Morrison', 1973, 2);
        [, $loans] = $this->repositories($sula);
        $loans->add($loan = $this->loan($sula, $this->amira, '2026-03-16'));
        $today = new DateTimeImmutable('2026-03-14');

        self::assertTrue($loans->markReminded($loan, null, $today));
        self::assertFalse($loans->markReminded($loan, null, $today), 'Another worker read "never reminded" too');
        self::assertSame('2026-03-14', $loans->find($loan->id())?->remindedOn?->format('Y-m-d'));
        self::assertTrue($loans->markReminded($loan, $today, new DateTimeImmutable('2026-03-17')), 'The next one');
    }
}
