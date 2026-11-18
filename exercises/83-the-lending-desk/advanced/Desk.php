<?php

declare(strict_types=1);

// A lending desk on a database (chapter 83), with the tables of the test:
//   books (title TEXT PRIMARY KEY, copies INTEGER NOT NULL)
//   loans (id INTEGER PRIMARY KEY, title TEXT, member TEXT, returned INTEGER NOT NULL DEFAULT 0)
// - lend($title, $member): the loan's id. One copy fewer on the shelf, and the loan, in one
//   transaction. A DeskError if there is no copy left, or the member has 3 loans that are out.
//   The copy is taken with one UPDATE that checks there is one (copies > 0), never read first.
// - giveBack($id): the loan is returned, and its copy back on the shelf, in one transaction.
//   A DeskError for an unknown loan, or one that is back already: then nothing changes.
// - out($member): how many of the member's loans are out.

final class DeskError extends RuntimeException {}

final readonly class Desk
{
    public const int MAX_LOANS = 3;

    public function __construct(private PDO $db) {}

    public function lend(string $title, string $member): int
    {
        return 0; // TODO
    }

    public function giveBack(int $id): void
    {
        // TODO
    }

    public function out(string $member): int
    {
        return 0; // TODO
    }
}
