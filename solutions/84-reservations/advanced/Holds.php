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
        if ($this->count('SELECT copies FROM books WHERE title = ?', [$title]) > 0) {
            throw new HoldError("\"$title\" is on the shelf");
        }
        $open = $this->count("SELECT COUNT(*) FROM holds WHERE title = ? AND member = ? AND status IN ('waiting', 'ready')", [$title, $member]);
        if ($open > 0) {
            throw new HoldError("$member has reserved \"$title\" already");
        }
        $this->db->prepare('INSERT INTO holds (title, member) VALUES (?, ?)')->execute([$title, $member]);

        return (int) $this->db->lastInsertId();
    }

    public function copyBack(string $title): ?string
    {
        return $this->inTransaction(fn(): ?string => $this->passOn($title));
    }

    public function cancel(int $id): ?string
    {
        return $this->inTransaction(function () use ($id): ?string {
            $find = $this->db->prepare("SELECT title, status FROM holds WHERE id = ? AND status IN ('waiting', 'ready')");
            $find->execute([$id]);
            /** @var array{title: string, status: string}|false $hold */
            $hold = $find->fetch(PDO::FETCH_ASSOC);
            if ($hold === false) {
                throw new HoldError("No open reservation number $id");
            }
            $this->db->prepare("UPDATE holds SET status = 'cancelled' WHERE id = ?")->execute([$id]);

            return $hold['status'] === 'ready' ? $this->passOn($hold['title']) : null;
        });
    }

    private function passOn(string $title): ?string
    {
        $claim = $this->db->prepare("UPDATE holds SET status = 'ready' WHERE id = ? AND status = 'waiting'");
        $first = $this->db->prepare("SELECT id, member FROM holds WHERE title = ? AND status = 'waiting' ORDER BY id LIMIT 1");
        while ($first->execute([$title]) && ($hold = $first->fetch(PDO::FETCH_ASSOC)) !== false) {
            /** @var array{id: int, member: string} $hold */
            $claim->execute([$hold['id']]);
            if ($claim->rowCount() === 1) {
                return $hold['member'];
            }
        }
        $this->db->prepare('UPDATE books SET copies = copies + 1 WHERE title = ?')->execute([$title]);

        return null;
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

    /** @param list<string> $params */
    private function count(string $sql, array $params): int
    {
        $query = $this->db->prepare($sql);
        $query->execute($params);

        return (int) $query->fetchColumn();
    }
}
