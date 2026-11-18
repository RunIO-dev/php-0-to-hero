<?php

declare(strict_types=1);

// A message queue on a database, for several workers at once (chapter 85), with the table of the test:
//   messages (id INTEGER PRIMARY KEY, body TEXT NOT NULL, tries INTEGER NOT NULL DEFAULT 0,
//             available_at INTEGER NOT NULL, taken_by TEXT, failed INTEGER NOT NULL DEFAULT 0)
// Times are Unix seconds, passed in, so the tests decide what "now" is. Each Queue is one worker, $worker.
// - push($body, $now): the new message's id; available at once.
// - take($now): the oldest message that is available (available_at <= $now), not taken and not failed,
//   claimed for this worker with an UPDATE that checks it is still not taken. If another worker took it
//   a moment ago, no row changes: try the next one. null if there is none: ['id' => …, 'body' => …].
// - done($id): the message is handled: delete it. A QueueError if this worker doesn't hold it.
// - fail($id, $now): one more try failed. After 3 tries it is failed for good (returns false);
//   otherwise it is free again from $now + 60 * 2^(tries - 1) seconds (returns true).
//   A QueueError if this worker doesn't hold it.

final class QueueError extends RuntimeException {}

final readonly class Queue
{
    public const int MAX_TRIES = 3;
    public const int RETRY_AFTER = 60;

    public function __construct(private PDO $db, private string $worker) {}

    public function push(string $body, int $now): int
    {
        $this->db->prepare('INSERT INTO messages (body, available_at) VALUES (?, ?)')->execute([$body, $now]);

        return (int) $this->db->lastInsertId();
    }

    /** @return array{id: int, body: string}|null */
    public function take(int $now): ?array
    {
        $next = $this->db->prepare('SELECT id, body FROM messages WHERE available_at <= ? AND taken_by IS NULL AND failed = 0 ORDER BY id LIMIT 1');
        $claim = $this->db->prepare('UPDATE messages SET taken_by = ? WHERE id = ? AND taken_by IS NULL');
        while ($next->execute([$now]) && ($message = $next->fetch(PDO::FETCH_ASSOC)) !== false) {
            /** @var array{id: int, body: string} $message */
            $claim->execute([$this->worker, $message['id']]);
            if ($claim->rowCount() === 1) {
                return ['id' => (int) $message['id'], 'body' => $message['body']];
            }
        }

        return null;
    }

    public function done(int $id): void
    {
        $delete = $this->db->prepare('DELETE FROM messages WHERE id = ? AND taken_by = ?');
        $delete->execute([$id, $this->worker]);
        if ($delete->rowCount() !== 1) {
            throw new QueueError("$this->worker doesn't hold message $id");
        }
    }

    public function fail(int $id, int $now): bool
    {
        $find = $this->db->prepare('SELECT tries FROM messages WHERE id = ? AND taken_by = ?');
        $find->execute([$id, $this->worker]);
        $tries = $find->fetchColumn();
        if ($tries === false) {
            throw new QueueError("$this->worker doesn't hold message $id");
        }
        $tries = (int) $tries + 1;
        $retry = $tries < self::MAX_TRIES;
        $this->db->prepare('UPDATE messages SET tries = ?, taken_by = NULL, failed = ?, available_at = ? WHERE id = ?')
            ->execute([$tries, $retry ? 0 : 1, $now + self::RETRY_AFTER * 2 ** ($tries - 1), $id]);

        return $retry;
    }
}
