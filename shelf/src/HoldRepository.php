<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;

/** Where the reservations are kept (chapter 84): a queue for each book. */
interface HoldRepository
{
    public function add(Hold $hold): void;

    /** A reservation by its number, open or not; null if there is none. */
    public function find(int $id): ?Hold;

    /** The member's open reservation of the book, waiting or ready, if there is one. */
    public function openFor(Book $book, Member $member): ?Hold;

    /** @return list<Hold> the member's open reservations, in the order they were made */
    public function ofMember(Member $member): array;

    /** @return list<Hold> every copy set aside at the desk, the soonest to expire first */
    public function ready(): array;

    /** @return list<Hold> the copies set aside whose days to collect them are over */
    public function expired(DateTimeImmutable $today): array;

    /** A waiting reservation's place in its book's queue, from 1. */
    public function position(Hold $hold): int;

    /** Whether anyone is in the book's queue. */
    public function isWaitedFor(Book $book): bool;

    /**
     * A copy of the book is free: the first in its queue gets it, set aside and ready, or, if nobody
     * is waiting, it goes back on the shelf. Returns the reservation it went to, or null.
     * The first in the queue is claimed with one UPDATE that checks it is still waiting, so two copies
     * at once never go to the same reservation.
     */
    public function passOn(Book $book, DateTimeImmutable $today): ?Hold;

    /**
     * Stores a reservation that ended, cancelled or expired, and passes its copy on, if one was set
     * aside for it: both, or neither.
     *
     * @return ?Hold the reservation the copy went to next, if any
     */
    public function release(Hold $hold, DateTimeImmutable $today): ?Hold;

    /** Stores a change to a reservation. */
    public function update(Hold $hold): void;

    /** Deletes the member's reservations, when the member deletes their account. Only closed ones are left by then. */
    public function forget(Member $member): void;

    /** Deletes the book's reservations, when the book is removed for good. Only closed ones are left by then. */
    public function forgetBook(Book $book): void;
}
