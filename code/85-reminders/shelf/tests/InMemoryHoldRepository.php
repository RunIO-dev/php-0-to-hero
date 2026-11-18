<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use Shelf\Book;
use Shelf\BookNotFound;
use Shelf\Hold;
use Shelf\HoldRepository;
use Shelf\HoldStatus;
use Shelf\Member;

use function Shelf\sameTitle;

/** Reservations for tests, in an array (chapter 84). A copy nobody waits for goes back to the books it was given. */
final class InMemoryHoldRepository implements HoldRepository
{
    /** @var list<Hold> */
    private array $holds = [];

    public function __construct(private readonly InMemoryBookRepository $books) {}

    public function add(Hold $hold): void
    {
        $this->holds[] = $hold;
        new \ReflectionProperty(Hold::class, 'id')->setValue($hold, count($this->holds)); // as the database would
    }

    public function find(int $id): ?Hold
    {
        return array_find($this->holds, fn(Hold $hold): bool => $hold->id() === $id);
    }

    public function openFor(Book $book, Member $member): ?Hold
    {
        return array_find($this->ofMember($member), fn(Hold $hold): bool => sameTitle($hold->book->title, $book->title));
    }

    public function ofMember(Member $member): array
    {
        return $this->where(fn(Hold $hold): bool => $this->same($hold->member, $member) && $hold->status->isOpen());
    }

    public function ready(): array
    {
        $ready = $this->where(fn(Hold $hold): bool => $hold->status === HoldStatus::Ready);
        usort($ready, fn(Hold $a, Hold $b): int => [$a->readyUntil, $a->book->title] <=> [$b->readyUntil, $b->book->title]);

        return $ready;
    }

    public function expired(DateTimeImmutable $today): array
    {
        return $this->where(fn(Hold $hold): bool => $hold->hasExpired($today));
    }

    public function position(Hold $hold): int
    {
        return count($this->where(fn(Hold $other): bool => $this->waitsFor($other, $hold->book) && $other->id() <= $hold->id()));
    }

    public function isWaitedFor(Book $book): bool
    {
        return $this->where(fn(Hold $hold): bool => $this->waitsFor($hold, $book)) !== [];
    }

    public function passOn(Book $book, DateTimeImmutable $today): ?Hold
    {
        $first = $this->where(fn(Hold $hold): bool => $this->waitsFor($hold, $book))[0] ?? null;
        if ($first !== null) {
            $first->ready($today);

            return $first;
        }
        $stored = $this->books->find($book->title) ?? throw new BookNotFound("There is no book titled \"$book->title\"");
        $stored->putBack();
        $this->books->update($stored);

        return null;
    }

    public function release(Hold $hold, DateTimeImmutable $today): ?Hold
    {
        return $hold->hadACopy() ? $this->passOn($hold->book, $today) : null;
    }

    public function update(Hold $hold): void {}

    public function markNotified(Hold $hold, DateTimeImmutable $today): bool
    {
        if ($hold->notifiedOn !== null) {
            return false;
        }
        $hold->notify($today);

        return true;
    }

    public function forget(Member $member): void
    {
        $this->holds = $this->where(fn(Hold $hold): bool => !$this->same($hold->member, $member));
    }

    public function forgetBook(Book $book): void
    {
        $this->holds = $this->where(fn(Hold $hold): bool => !sameTitle($hold->book->title, $book->title));
    }

    /**
     * @param callable(Hold): bool $matches
     * @return list<Hold> in the order they were made
     */
    private function where(callable $matches): array
    {
        return array_values(array_filter($this->holds, $matches));
    }

    private function waitsFor(Hold $hold, Book $book): bool
    {
        return $hold->status === HoldStatus::Waiting && sameTitle($hold->book->title, $book->title);
    }

    private function same(Member $a, Member $b): bool
    {
        return mb_strtolower($a->name) === mb_strtolower($b->name);
    }
}
