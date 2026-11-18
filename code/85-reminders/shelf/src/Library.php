<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * The library: its books, members and loans, and the rules for lending and adding. It has
 * repositories; it isn't one. Chapter 56: members and loans are stored too. Chapter 84: and
 * reservations, a queue for each book whose copies are all out.
 */
final readonly class Library
{
    /** The most copies of one book the form accepts: more is a typing mistake. */
    public const int MOST_COPIES = 999;

    public function __construct(
        private BookRepository $books,
        private MemberRepository $members,
        private LoanRepository $loans,
        private HoldRepository $holds,
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
    public function removeMember(Member $member, DateTimeImmutable $today = new DateTimeImmutable()): void
    {
        $count = count($this->loans->forMember($member->name));
        if ($count > 0) {
            throw new InvalidArgumentException('Please return your ' . ($count === 1 ? 'book' : "$count books") . ' first: then you can delete your account.');
        }
        foreach ($this->holds->ofMember($member) as $hold) { // chapter 84: a copy set aside goes to the next in line
            $hold->cancel();
            $this->holds->release($hold, $today);
        }
        $this->holds->forget($member);
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

    /**
     * Chapter 83: the book is back on the shelf. Chapter 84: or set aside for the first in its queue,
     * whose reservation comes back with the loan.
     *
     * @return array{Loan, ?Hold}
     */
    public function giveBack(int $id, DateTimeImmutable $today): array
    {
        $loan = $this->loan($id);

        return [$loan, $this->loans->giveBack($loan, $today)];
    }

    /** Chapter 83: one more loan period, by Loan's rules. Chapter 84: not while someone waits for the book. */
    public function renew(int $id, DateTimeImmutable $today): Loan
    {
        $loan = $this->loan($id);
        if (!$loan->isReturned() && $this->holds->isWaitedFor($loan->book)) {
            throw new InvalidArgumentException("\"{$loan->book->title}\" is reserved by another member, so it can't be extended: please bring it back by {$loan->due->format('l, j F Y')}.");
        }
        $loan->renew($today);
        $this->loans->update($loan);

        return $loan;
    }

    /**
     * Chapter 84: the loans of these whose book someone is waiting for, which can't be extended.
     *
     * @param list<Loan> $loans
     * @return list<int> their numbers
     */
    public function reserved(array $loans): array
    {
        return array_values(array_map(
            fn(Loan $loan): int => $loan->id(),
            array_filter($loans, fn(Loan $loan): bool => $this->holds->isWaitedFor($loan->book)),
        ));
    }

    /**
     * Chapter 84: $member reserves a book whose copies are all out, and joins its queue. Not a book
     * on the shelf, one they have, or one they reserved already; and not while they are blocked.
     */
    public function reserve(string $title, Member $member, DateTimeImmutable $today): Hold
    {
        $book = $this->find($title);
        if ($member->blocked) {
            throw new InvalidArgumentException("$member->name is blocked, and can't reserve books.");
        }
        if ($book->isAvailable()) {
            throw new InvalidArgumentException("\"$book->title\" is on the shelf: borrow it at the desk, no need to reserve it.");
        }
        if (array_any($this->loans->forMember($member->name), fn(Loan $loan): bool => sameTitle($loan->book->title, $book->title))) {
            throw new InvalidArgumentException("$member->name has \"$book->title\" already.");
        }
        if ($this->holds->openFor($book, $member) !== null) {
            throw new InvalidArgumentException("$member->name has reserved \"$book->title\" already.");
        }
        $hold = new Hold($book, $member, $today);
        $this->holds->add($hold);

        return $hold;
    }

    /**
     * Chapter 84: the member's open reservations, each with its place in the queue (0 once a copy is
     * set aside for it).
     *
     * @return list<array{hold: Hold, position: int}>
     */
    public function holdsOf(Member $member): array
    {
        return array_map(
            fn(Hold $hold): array => ['hold' => $hold, 'position' => $hold->status === HoldStatus::Waiting ? $this->holds->position($hold) : 0],
            $this->holds->ofMember($member),
        );
    }

    /** Chapter 84: the member's open reservation of a book, if there is one. */
    public function holdOf(Book $book, Member $member): ?Hold
    {
        return $this->holds->openFor($book, $member);
    }

    /**
     * Chapter 84: the member cancels their reservation; a copy set aside for it goes to the next in
     * line. Someone else's reservation is as if it didn't exist.
     *
     * @return array{Hold, ?Hold} the cancelled reservation, and the one its copy went to
     */
    public function cancelHold(int $id, Member $member, DateTimeImmutable $today): array
    {
        $hold = $this->holds->find($id);
        if ($hold === null || mb_strtolower($hold->member->name) !== mb_strtolower($member->name) || !$hold->status->isOpen()) {
            throw new HoldNotFound("You have no reservation number $id");
        }
        $hold->cancel();

        return [$hold, $this->holds->release($hold, $today)];
    }

    /** @return list<Hold> chapter 84: the copies set aside at the desk, the soonest to expire first */
    public function readyHolds(): array
    {
        return $this->holds->ready();
    }

    /**
     * Chapter 84: the copies nobody came for in time go to the next in line, or back on the shelf.
     * For a timer once a day (chapter 85); bin/console shelf:holds runs it.
     *
     * @return list<array{Hold, ?Hold}> each expired reservation, and the one its copy went to
     */
    public function expireHolds(DateTimeImmutable $today): array
    {
        $done = [];
        foreach ($this->holds->expired($today) as $hold) {
            $hold->expire($today);
            $done[] = [$hold, $this->holds->release($hold, $today)];
        }

        return $done;
    }

    /**
     * Chapter 85: what needs a reminder today: loans due soon or overdue, and copies set aside that
     * nobody has told their member about. Only members with a confirmed address get email.
     *
     * @return list<array{Loan|Hold, Reminder}>
     */
    public function remindersDue(DateTimeImmutable $today): array
    {
        $due = [];
        foreach ($this->loans->current() as $loan) {
            $kind = $loan->reminderDue($today);
            if ($kind !== null && $loan->member->canBeMailed()) {
                $due[] = [$loan, $kind];
            }
        }
        foreach ($this->holds->ready() as $hold) {
            if ($hold->needsPickupMail() && $hold->member->canBeMailed()) {
                $due[] = [$hold, Reminder::Pickup];
            }
        }

        return $due;
    }

    /**
     * Chapter 85: claims a reminder before it is sent. True for the one caller that may send it; false
     * if it was sent already, by an earlier run of the same job or by another worker at the same time.
     */
    public function claimReminder(Loan|Hold $about, DateTimeImmutable $today): bool
    {
        return $about instanceof Loan
            ? $this->loans->markReminded($about, $about->remindedOn, $today)
            : $this->holds->markNotified($about, $today);
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
        // Chapter 84: a copy set aside for this member is theirs, though none is on the shelf.
        $hold = $this->holds->openFor($book, $member);
        if ($hold?->status !== HoldStatus::Ready && !$book->isAvailable()) {
            throw new NoCopyLeft("All copies of \"$book->title\" are on loan");
        }
        $loan = Loan::start($book, $member, $today);
        $this->loans->add($loan, $hold); // stores the loan and takes the copy, together (chapter 57); collects the reservation

        return $loan;
    }

    /** @return list<Loan> the member's loans, the soonest due first */
    public function loansOf(string $name): array
    {
        return $this->loans->forMember($this->member($name)->name);
    }

    /**
     * Sets how many copies the library has of a book. $copies is as typed into the form.
     * Chapter 84: copies added while members wait go to the queue first, one by one, as a copy that
     * comes back does.
     *
     * @return array{Book, list<Hold>} the book, and the reservations that got one of the new copies
     */
    public function changeCopies(string $title, string $copies, DateTimeImmutable $today = new DateTimeImmutable()): array
    {
        $book = $this->find($title);
        $count = filter_var($copies, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => self::MOST_COPIES]]);
        if ($count === false) {
            throw new InvalidArgumentException('Copies must be a whole number from 0 to ' . self::MOST_COPIES . '.');
        }
        $added = max(0, $count - $book->copies);
        $book->changeCopies($count - $added);
        $this->books->update($book);
        $served = [];
        for ($i = 0; $i < $added; $i++) {
            $hold = $this->holds->passOn($book, $today);
            if ($hold !== null) {
                $served[] = $hold;
            }
        }

        return [$this->find($book->title), $served]; // read again: passOn() may have put copies on the shelf
    }

    /**
     * Takes a book off the shelf for good, and returns it. A book that is lent can't go: its loans need it.
     * Chapter 83: its returned loans, its history, go with it, as a member's go with their account.
     */
    public function remove(string $title): Book
    {
        $book = $this->find($title);
        if ($this->loans->isOnLoan($book->title)) {
            throw new InvalidArgumentException("\"$book->title\" is on loan, so it can't be removed.");
        }
        $setAside = array_any($this->holds->ready(), fn(Hold $hold): bool => sameTitle($hold->book->title, $book->title));
        if ($setAside || $this->holds->isWaitedFor($book)) { // chapter 84
            throw new InvalidArgumentException("\"$book->title\" is reserved, so it can't be removed.");
        }
        $this->loans->forgetBook($book);
        $this->holds->forgetBook($book);
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
