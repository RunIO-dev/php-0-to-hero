<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\Library;
use Shelf\Loan;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\NoCopyLeft;
use Shelf\Role;

final class LibraryTest extends TestCase
{
    private InMemoryBookRepository $books;
    private InMemoryLoanRepository $loans;
    private Library $library;

    protected function setUp(): void
    {
        $this->books = new InMemoryBookRepository([
            new Book('Kindred', 'Octavia E. Butler', 1979, copies: 1),
            new Book('Things Fall Apart', 'Chinua Achebe', 1958, copies: 2),
        ]);
        $this->loans = new InMemoryLoanRepository();
        $this->library = new Library($this->books, new InMemoryMemberRepository([new Member('Amira', Role::Reader)]), $this->loans);
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
        $this->expectException(InvalidArgumentException::class);
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
}
