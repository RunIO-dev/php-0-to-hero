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
        return 0; // TODO
    }

    /** @return array{id: int, body: string}|null */
    public function take(int $now): ?array
    {
        return null; // TODO
    }

    public function done(int $id): void
    {
        // TODO
    }

    public function fail(int $id, int $now): bool
    {
        return false; // TODO
    }
}
