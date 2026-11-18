<?php

declare(strict_types=1);

// A limit on emails (chapter 82), as a sliding window: a key (a visitor's IP address) may send at most
// $limit messages in any $seconds; Symfony's "sliding_window" works much like it.
//   allow($key, $now): true, and the message counts, if fewer than $limit of the key's messages were
//   sent in the $seconds before $now ($now - $seconds < time ≤ $now); else false, and nothing counts.
//   retryAfter($key, $now): seconds until the key may send again; 0 if it may now.
// Keys don't share their counts. Times older than the window are forgotten, so the memory a key
// takes never grows beyond $limit times.

final class MailLimit
{
    /** @var array<string, list<int>> each key's send times, oldest first */
    private array $sent = [];

    public function __construct(private readonly int $limit, private readonly int $seconds) {}

    public function allow(string $key, int $now): bool
    {
        return true; // TODO
    }

    public function retryAfter(string $key, int $now): int
    {
        return 0; // TODO
    }

    /** How many times are kept for a key: for the tests. */
    public function kept(string $key): int
    {
        return count($this->sent[$key] ?? []);
    }
}
