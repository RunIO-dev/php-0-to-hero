<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\BookExists;
use Shelf\BookNotFound;
use Shelf\BookQuery;
use Shelf\Hold;
use Shelf\HoldNotFound;
use Shelf\HoldStatus;
use Shelf\Library;
use Shelf\LoanNotFound;
use Shelf\Loan;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\NoCopyLeft;
use Shelf\Reminder;
use Shelf\Role;

final class LibraryTest extends TestCase
{
    private InMemoryBookRepository $books;
    private InMemoryLoanRepository $loans;
    private InMemoryMemberRepository $members;
    private Library $library;

    protected function setUp(): void
    {
        $this->books = new InMemoryBookRepository([
            new Book('Kindred', 'Octavia E. Butler', 1979, copies: 1),
            new Book('Things Fall Apart', 'Chinua Achebe', 1958, copies: 2),
        ]);
        $holds = new InMemoryHoldRepository($this->books);
        $this->loans = new InMemoryLoanRepository($this->books, $holds);
        $this->members = new InMemoryMemberRepository([new Member('Amira', Role::Reader), new Member('Ben', Role::Reader)]);
        $this->library = new Library($this->books, $this->members, $this->loans, $holds);
    }

    public function testLendingSavesOneCopyLess(): void
    {
        $this->library->lend('things fall apart', 'amira', new DateTimeImmutable('2026-03-02'));

        $book = $this->library->books()[1];
        self::assertSame('Things Fall Apart', $book->title);
        self::assertSame(1, $book->copies);
    }

    public function testTheLastCopyCanBeLentOnlyOnce(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $this->expectException(NoCopyLeft::class);
        $this->expectExceptionMessage('All copies of "Kindred" are on loan');

        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
    }

    public function testAnUnknownBookCantBeLent(): void
    {
        $this->expectException(BookNotFound::class);
        $this->expectExceptionMessage('There is no book titled "Dune"');

        $this->library->lend('Dune', 'Amira', new DateTimeImmutable('2026-03-02'));
    }

    public function testAnUnknownMemberCantBorrow(): void
    {
        $this->expectException(MemberNotFound::class);

        $this->library->lend('Kindred', 'Nobody', new DateTimeImmutable('2026-03-02'));
    }

    public function testATitleCanBeAddedOnlyOnceInAnyCase(): void
    {
        $this->expectException(BookExists::class);
        $this->expectExceptionMessage('"Kindred" is already on the shelf');

        $this->library->add(['  KINDRED ', 'Octavia E. Butler', '1979']);
    }

    public function testAnAddedBookIsSavedInOrder(): void
    {
        $this->library->add(['Beloved', 'Toni Morrison', '1987']);

        self::assertSame(['Beloved', 'Kindred', 'Things Fall Apart'], array_column($this->library->books(), 'title'));
    }

    public function testTheCopiesCanBeChanged(): void
    {
        $this->library->changeCopies('kindred', '3');

        self::assertSame(3, $this->library->find('Kindred')->copies);
    }

    /** @return iterable<string, array{string}> */
    public static function wrongCopies(): iterable
    {
        yield 'less than none' => ['-1'];
        yield 'not a number' => ['two'];
        yield 'a fraction' => ['1.5'];
        yield 'too many' => ['1000'];
        yield 'nothing' => [''];
    }

    #[DataProvider('wrongCopies')]
    public function testTheCopiesMustBeAWholeNumberInRange(string $copies): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Copies must be a whole number from 0 to 999.');

        $this->library->changeCopies('Kindred', $copies);
    }

    /** Chapter 84: a new copy goes to whoever waits, before the shelf. */
    public function testANewCopyGoesToTheQueueFirst(): void
    {
        $this->benWaitsForKindred();

        [$book, $served] = $this->library->changeCopies('Kindred', '2', new DateTimeImmutable('2026-03-04'));

        self::assertSame(['Ben'], array_map(fn(Hold $hold): string => $hold->member->name, $served));
        self::assertSame(1, $book->copies);
    }

    public function testARemovedBookIsGone(): void
    {
        $removed = $this->library->remove('KINDRED');

        self::assertSame('Kindred', $removed->title);
        self::assertSame(['Things Fall Apart'], array_column($this->library->books(), 'title'));
    }

    public function testAnUnknownBookCantBeRemoved(): void
    {
        $this->expectException(BookNotFound::class);

        $this->library->remove('Dune');
    }

    public function testALoanIsStoredForTheMember(): void
    {
        $this->library->lend('Things Fall Apart', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-01'));

        $loans = $this->library->loansOf('amira');

        self::assertSame(['Kindred', 'Things Fall Apart'], array_map(fn(Loan $loan): string => $loan->book->title, $loans));
        self::assertSame('2026-03-15', $loans[0]->due->format('Y-m-d'));
    }

    public function testOnlyAMemberHasLoans(): void
    {
        $this->expectException(MemberNotFound::class);

        $this->library->loansOf('Nobody');
    }

    public function testABookOnLoanCantBeRemoved(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Kindred" is on loan, so it can\'t be removed.');

        $this->library->remove('Kindred');
    }

    /** Chapter 83: a book that came back can go, and its history with it. */
    public function testABookThatCameBackCanBeRemoved(): void
    {
        $loan = $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->giveBack($loan->id(), new DateTimeImmutable('2026-03-05'));

        $this->library->remove('Kindred');

        self::assertSame([], $this->library->loansOf('Amira'));
        self::assertSame(['Things Fall Apart'], array_column($this->library->books(), 'title'));
    }

    /** Chapter 82: a member deletes their account. */
    public function testAMemberCanLeave(): void
    {
        $this->library->removeMember($this->library->findMember('Amira') ?? self::fail('No Amira'));

        self::assertNull($this->library->findMember('Amira'));
    }

    public function testAMemberWithBooksCantLeave(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->lend('Things Fall Apart', 'Amira', new DateTimeImmutable('2026-03-02'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Please return your 2 books first: then you can delete your account.');
        $this->library->removeMember($this->library->findMember('Amira') ?? self::fail('No Amira'));
    }

    /** Chapter 83: a blocked member can't borrow. */
    public function testABlockedMemberCantBorrow(): void
    {
        $staff = new Member('Jonas', Role::Staff);
        $this->library->setBlocked('Amira', true, $staff);

        try {
            $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
            self::fail('Lent to a blocked member');
        } catch (InvalidArgumentException $e) {
            self::assertSame("Amira is blocked, and can't borrow books.", $e->getMessage());
        }
        $this->library->setBlocked('amira', false, $staff);
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        self::assertCount(1, $this->library->loansOf('Amira'));
    }

    public function testAReaderHasFiveBooksAtMost(): void
    {
        foreach (range(1, 6) as $n) {
            $this->books->add(new Book("Story $n", 'Ana Writer', 2000 + $n));
        }
        foreach (range(1, 5) as $n) {
            $this->library->lend("Story $n", 'Amira', new DateTimeImmutable('2026-03-02'));
        }

        $this->expectExceptionMessage('Amira has 5 books already: that is the most a reader may have.');
        $this->library->lend('Story 6', 'Amira', new DateTimeImmutable('2026-03-02'));
    }

    public function testAReturnedBookCanBeLentAgain(): void
    {
        $loan = $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->giveBack($loan->id(), new DateTimeImmutable('2026-03-05'));

        self::assertSame([], $this->library->currentLoans());
        self::assertSame(1, $this->library->find('Kindred')->copies);
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-05'));
    }

    public function testNobodyChangesTheirOwnRole(): void
    {
        $this->expectExceptionMessage("You can't change your own account here");

        $this->library->changeRole('AMIRA', Role::Staff, $this->library->findMember('Amira') ?? self::fail('No Amira'));
    }

    public function testAnUnknownLoanIsNotFound(): void
    {
        $this->expectException(LoanNotFound::class);

        $this->library->giveBack(42, new DateTimeImmutable('2026-03-05'));
    }

    public function testAPageKnowsHowManyPagesThereAre(): void
    {
        $first = $this->library->page(new BookQuery(), 1, 1);
        $last = $this->library->page(new BookQuery(), 2, 1);

        self::assertSame([['Kindred'], 2, 2], [array_column($first['books'], 'title'), $first['pages'], $first['total']]);
        self::assertSame(['Things Fall Apart'], array_column($last['books'], 'title'));
        self::assertSame(['books' => [], 'pages' => 1, 'total' => 0], $this->library->page(new BookQuery('Morrison'), 1, 20), 'Nothing found is still one page');
    }

    public function testPagesCountFromOne(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->library->page(new BookQuery(), 0, 20);
    }

    private function member(string $name): Member
    {
        return $this->members->find($name) ?? self::fail("No member $name");
    }

    /** Chapter 84: Amira has the only copy of Kindred, and Ben reserves it. */
    private function benWaitsForKindred(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->reserve('Kindred', $this->member('Ben'), new DateTimeImmutable('2026-03-03'));
    }

    public function testABookOnTheShelfIsntReserved(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Things Fall Apart" is on the shelf: borrow it at the desk, no need to reserve it.');

        $this->library->reserve('Things Fall Apart', $this->member('Ben'), new DateTimeImmutable('2026-03-02'));
    }

    public function testNobodyReservesWhatTheyHave(): void
    {
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));

        $this->expectExceptionMessage('Amira has "Kindred" already.');

        $this->library->reserve('kindred', $this->member('Amira'), new DateTimeImmutable('2026-03-02'));
    }

    public function testAReturnedCopyGoesToTheFirstInLine(): void
    {
        $this->benWaitsForKindred();

        [, $hold] = $this->library->giveBack(1, new DateTimeImmutable('2026-03-05'));

        self::assertSame(['Ben', HoldStatus::Ready], [$hold?->member->name, $hold?->status]);
        self::assertSame(0, $this->library->find('Kindred')->copies, 'Set aside, not on the shelf');
        try {
            $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-05'));
            self::fail('The copy is Ben\'s');
        } catch (NoCopyLeft) {
        }
        $this->library->lend('Kindred', 'Ben', new DateTimeImmutable('2026-03-06'));
        self::assertSame(HoldStatus::Collected, $hold?->status);
        self::assertSame(0, $this->library->find('Kindred')->copies);
    }

    public function testNoExtendingWhileSomeoneWaits(): void
    {
        $this->benWaitsForKindred();

        $this->expectExceptionMessage('"Kindred" is reserved by another member, so it can\'t be extended: please bring it back by Monday, 16 March 2026.');

        $this->library->renew(1, new DateTimeImmutable('2026-03-10'));
    }

    public function testAnUncollectedCopyGoesBackOnTheShelf(): void
    {
        $this->benWaitsForKindred();
        $this->library->giveBack(1, new DateTimeImmutable('2026-03-05'));

        self::assertSame([], $this->library->expireHolds(new DateTimeImmutable('2026-03-12')), 'The last day counts');
        [[$expired, $next]] = $this->library->expireHolds(new DateTimeImmutable('2026-03-13'));

        self::assertSame([HoldStatus::Expired, null], [$expired->status, $next]);
        self::assertSame(1, $this->library->find('Kindred')->copies);
    }

    public function testACancelledCopyGoesToTheNextInLine(): void
    {
        $this->benWaitsForKindred();
        $this->library->giveBack(1, new DateTimeImmutable('2026-03-05'));
        $this->library->reserve('Kindred', $this->member('Amira'), new DateTimeImmutable('2026-03-05'));
        $bens = $this->library->holdsOf($this->member('Ben'))[0]['hold'];

        [, $next] = $this->library->cancelHold($bens->id(), $this->member('Ben'), new DateTimeImmutable('2026-03-06'));

        self::assertSame(['Amira', '2026-03-13'], [$next?->member->name, $next?->readyUntil?->format('Y-m-d')]);
    }

    public function testOnlyYourOwnReservationCanBeCancelled(): void
    {
        $this->benWaitsForKindred();

        $this->expectException(HoldNotFound::class);

        $this->library->cancelHold(1, $this->member('Amira'), new DateTimeImmutable('2026-03-04'));
    }

    public function testAReservedBookStays(): void
    {
        $this->benWaitsForKindred();
        $this->library->giveBack(1, new DateTimeImmutable('2026-03-05'));

        $this->expectExceptionMessage('"Kindred" is reserved, so it can\'t be removed.');

        $this->library->remove('Kindred');
    }

    public function testALeavingMembersCopyGoesToTheNext(): void
    {
        $this->benWaitsForKindred();
        $this->library->giveBack(1, new DateTimeImmutable('2026-03-05'));

        $this->library->removeMember($this->member('Ben'), new DateTimeImmutable('2026-03-06'));

        self::assertSame(1, $this->library->find('Kindred')->copies, 'Nobody else waits: back on the shelf');
    }

    /** Chapter 85: Ben has a confirmed address; Amira has none, so she gets no email. */
    public function testRemindersGoOnlyToConfirmedAddressesAndOnce(): void
    {
        $ben = $this->member('Ben');
        $ben->confirmEmail('ben@example.org');
        $this->library->lend('Kindred', 'Amira', new DateTimeImmutable('2026-03-02'));
        $this->library->lend('Things Fall Apart', 'Ben', new DateTimeImmutable('2026-03-02'));
        $today = new DateTimeImmutable('2026-03-14');

        $due = $this->library->remindersDue($today);

        self::assertCount(1, $due);
        [$loan, $kind] = $due[0];
        self::assertSame(['Ben', Reminder::DueSoon], [$loan->member->name, $kind]);
        self::assertTrue($this->library->claimReminder($loan, $today));
        self::assertSame([], $this->library->remindersDue($today), 'Claimed: not due any more');
    }

    public function testACopySetAsideNeedsAPickupMail(): void
    {
        $ben = $this->member('Ben');
        $ben->confirmEmail('ben@example.org');
        $this->benWaitsForKindred();
        $this->library->giveBack(1, new DateTimeImmutable('2026-03-05'));

        $due = $this->library->remindersDue(new DateTimeImmutable('2026-03-05'));

        self::assertSame(Reminder::Pickup, $due[0][1] ?? null);
    }
}
