<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * The library: its books, members and loans, and the rules for lending and adding. It has
 * repositories; it isn't one. Chapter 56: members and loans are stored too.
 */
final readonly class Library
{
    /** The most copies of one book the form accepts: more is a typing mistake. */
    public const int MOST_COPIES = 999;

    public function __construct(
        private BookRepository $books,
        private MemberRepository $members,
        private LoanRepository $loans,
    ) {}

    /** @return list<Book> every book, sorted by title */
    public function books(): array
    {
        $books = $this->books->all();
        usort($books, fn(Book $a, Book $b): int => strcasecmp($a->title, $b->title));

        return $books;
    }

    /**
     * Every title that contains the search text, in any case, optionally only by one author.
     *
     * @return list<string>
     */
    public function search(string $text, ?string $author = null): array
    {
        $contains = fn(string $haystack, string $part): bool
            => str_contains(mb_strtolower($haystack), mb_strtolower($part));
        $found = array_filter(
            $this->books->all(),
            fn(Book $book): bool => $contains($book->title, $text)
                && ($author === null || $contains($book->author, $author)),
        );

        return array_column($found, 'title');
    }

    /**
     * One page of the books that match the query, in its order (chapter 70), and how many pages
     * there are. $page counts from 1.
     *
     * Chapter 81: and how many books match in all.
     *
     * @return array{books: list<Book>, pages: int, total: int}
     */
    public function page(BookQuery $query, int $page, int $perPage): array
    {
        if ($page < 1 || $perPage < 1) {
            throw new InvalidArgumentException('Pages count from 1, and hold at least one book');
        }

        $total = $this->books->count($query);

        return [
            'books' => $this->books->page($query, ($page - 1) * $perPage, $perPage),
            'pages' => max(1, (int) ceil($total / $perPage)),
            'total' => $total,
        ];
    }

    /**
     * A member deletes their account (chapter 82). Not while they have books: the library would lose
     * track of them. Their API tokens go with them.
     */
    public function removeMember(Member $member): void
    {
        $count = count($this->loans->forMember($member->name));
        if ($count > 0) {
            throw new InvalidArgumentException('Please return your ' . ($count === 1 ? 'book' : "$count books") . ' first: then you can delete your account.');
        }
        $this->loans->forget($member); // the books they had are history; the history goes with them
        $this->members->remove($member);
    }

    /** @return list<Loan> chapter 83: every loan that isn't back, the soonest due first */
    public function currentLoans(): array
    {
        return $this->loans->current();
    }

    /** Chapter 83: a loan by its number, from a form. */
    public function loan(int $id): Loan
    {
        return $this->loans->find($id) ?? throw new LoanNotFound("There is no loan number $id");
    }

    /** Chapter 83: the book is back on the shelf. */
    public function giveBack(int $id, DateTimeImmutable $today): Loan
    {
        $loan = $this->loan($id);
        $this->loans->giveBack($loan, $today);

        return $loan;
    }

    /** Chapter 83: one more loan period, by Loan's rules. */
    public function renew(int $id, DateTimeImmutable $today): Loan
    {
        $loan = $this->loan($id);
        $loan->renew($today);
        $this->loans->update($loan);

        return $loan;
    }

    /** @return list<Member> chapter 83: every member, by name */
    public function members(): array
    {
        return $this->members->all();
    }

    /** Chapter 83: $by blocks or unblocks a member; never themselves, so the desk always has someone. */
    public function setBlocked(string $name, bool $blocked, Member $by): Member
    {
        $member = $this->otherMember($name, $by);
        $blocked ? $member->block() : $member->unblock();
        $this->members->update($member);

        return $member;
    }

    /** Chapter 83: $by gives a member another role; never themselves, so staff can't lock out the last of them. */
    public function changeRole(string $name, Role $role, Member $by): Member
    {
        $member = $this->otherMember($name, $by);
        $member->changeRole($role);
        $this->members->update($member);

        return $member;
    }

    private function otherMember(string $name, Member $by): Member
    {
        $member = $this->member($name);
        if ($member === $by || mb_strtolower($member->name) === mb_strtolower($by->name)) {
            throw new InvalidArgumentException('You can\'t change your own account here: ask another member of staff.');
        }

        return $member;
    }

    /** The book with this title, in any case. */
    public function find(string $title): Book
    {
        return $this->books->find($title) ?? throw new BookNotFound("There is no book titled \"$title\"");
    }

    public function lend(string $title, string $name, DateTimeImmutable $today): Loan
    {
        $member = $this->member($name);
        $book = $this->find($title);
        // Chapter 83: a blocked member can't borrow, and nobody more than their role allows.
        if ($member->blocked) {
            throw new InvalidArgumentException("$member->name is blocked, and can't borrow books.");
        }
        $has = count($this->loans->forMember($member->name));
        if ($has >= $member->role->maxLoans()) {
            throw new InvalidArgumentException("$member->name has $has books already: that is the most a {$member->role->value} may have.");
        }
        if (!$book->isAvailable()) {
            throw new NoCopyLeft("All copies of \"$book->title\" are on loan");
        }
        $loan = Loan::start($book, $member, $today);
        $this->loans->add($loan); // stores the loan and takes the copy, together (chapter 57)

        return $loan;
    }

    /** @return list<Loan> the member's loans, the soonest due first */
    public function loansOf(string $name): array
    {
        return $this->loans->forMember($this->member($name)->name);
    }

    /** Sets how many copies the library has of a book. $copies is as typed into the form. */
    public function changeCopies(string $title, string $copies): Book
    {
        $book = $this->find($title);
        $count = filter_var($copies, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => self::MOST_COPIES]]);
        if ($count === false) {
            throw new InvalidArgumentException('Copies must be a whole number from 0 to ' . self::MOST_COPIES . '.');
        }
        $book->changeCopies($count);
        $this->books->update($book);

        return $book;
    }

    /** Takes a book off the shelf for good, and returns it. A book that is lent can't go: its loans need it. */
    public function remove(string $title): Book
    {
        $book = $this->find($title);
        if ($this->loans->isOnLoan($book->title)) {
            throw new InvalidArgumentException("\"$book->title\" is on loan, so it can't be removed.");
        }
        $this->books->remove($book->title);

        return $book;
    }

    /** @param list<string> $fields title, author, year and, optionally, the ISBN */
    public function add(array $fields): Book
    {
        $books = $this->books->all();
        $new = $this->newBook($fields);
        foreach ($books as $book) {
            $sameIsbn = $new->isbn !== null && $book->isbn !== null && $book->isbn->equals($new->isbn);
            if ($sameIsbn || sameTitle($book->title, $new->title)) {
                throw new BookExists("\"$book->title\" is already on the shelf");
            }
        }
        $this->books->add($new);

        return $new;
    }

    /** @param list<string> $fields */
    private function newBook(array $fields): Book
    {
        $title = tidy($fields[0] ?? '');
        $author = tidy($fields[1] ?? '');
        $range = ['min_range' => Book::FIRST_YEAR, 'max_range' => Book::LAST_YEAR];
        $year = filter_var($fields[2] ?? '', FILTER_VALIDATE_INT, ['options' => $range]);
        if ($title === '' || $author === '' || $year === false) {
            throw new InvalidArgumentException('A book needs a title, an author and a year from ' . implode(' to ', $range) . '.');
        }

        return new Book($title, $author, $year, isbn: isset($fields[3]) ? new Isbn($fields[3]) : null);
    }

    /** The member with this name, or null: for the website, which knows a member by the name in the session (chapter 63). */
    public function findMember(string $name): ?Member
    {
        return $this->members->find($name);
    }

    private function member(string $name): Member
    {
        return $this->members->find($name) ?? throw new MemberNotFound("There is no member named \"$name\"");
    }
}
