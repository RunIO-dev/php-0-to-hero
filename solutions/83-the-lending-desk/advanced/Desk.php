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
        return $this->inTransaction(function () use ($title, $member): int {
            if ($this->out($member) >= self::MAX_LOANS) {
                throw new DeskError("$member has " . self::MAX_LOANS . ' books already');
            }
            $take = $this->db->prepare('UPDATE books SET copies = copies - 1 WHERE title = ? AND copies > 0');
            $take->execute([$title]);
            if ($take->rowCount() === 0) {
                throw new DeskError("No copy of $title is on the shelf");
            }
            $this->db->prepare('INSERT INTO loans (title, member) VALUES (?, ?)')->execute([$title, $member]);

            return (int) $this->db->lastInsertId();
        });
    }

    public function giveBack(int $id): void
    {
        $this->inTransaction(function () use ($id): void {
            $back = $this->db->prepare('UPDATE loans SET returned = 1 WHERE id = ? AND returned = 0');
            $back->execute([$id]);
            if ($back->rowCount() === 0) {
                throw new DeskError("Loan $id isn't out");
            }
            $this->db->prepare('UPDATE books SET copies = copies + 1 WHERE title = (SELECT title FROM loans WHERE id = ?)')->execute([$id]);
        });
    }

    public function out(string $member): int
    {
        $count = $this->db->prepare('SELECT COUNT(*) FROM loans WHERE member = ? AND returned = 0');
        $count->execute([$member]);

        return (int) $count->fetchColumn();
    }

    /**
     * @template T
     * @param callable(): T $work
     * @return T
     */
    private function inTransaction(callable $work): mixed
    {
        $this->db->beginTransaction();
        try {
            $result = $work();
            $this->db->commit();

            return $result;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
