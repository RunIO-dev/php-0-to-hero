<?php

declare(strict_types=1);

// Reservations on a database (chapter 84), with the tables of the test:
//   books (title TEXT PRIMARY KEY, copies INTEGER NOT NULL)          copies: on the shelf, free
//   holds (id INTEGER PRIMARY KEY, title TEXT, member TEXT, status TEXT NOT NULL DEFAULT 'waiting')
// - reserve($title, $member): the reservation's id. A HoldError if a copy is on the shelf, or the
//   member has an open reservation (waiting or ready) of the book already.
// - copyBack($title): a copy is free. The first waiting reservation becomes 'ready', and its member is
//   returned; if nobody waits, the copy goes on the shelf (copies + 1), and null is returned.
//   The reservation is claimed with an UPDATE that checks it is still 'waiting': if no row changed,
//   another desk was quicker, so try the next one. One transaction.
// - cancel($id): a waiting or ready reservation is 'cancelled'; if it was ready, its copy goes on with
//   copyBack(), in the same transaction. Returns who got the copy, or null. A HoldError otherwise.

final class HoldError extends RuntimeException {}

final readonly class Holds
{
    public function __construct(private PDO $db) {}

    public function reserve(string $title, string $member): int
    {
        return 0; // TODO
    }

    public function copyBack(string $title): ?string
    {
        return null; // TODO
    }

    public function cancel(int $id): ?string
    {
        return null; // TODO
    }
}
