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
        $recent = $this->recent($key, $now);
        if (count($recent) >= $this->limit) {
            return false;
        }
        $recent[] = $now;
        $this->sent[$key] = $recent;

        return true;
    }

    public function retryAfter(string $key, int $now): int
    {
        $recent = $this->recent($key, $now);

        // The oldest time in the window leaves it $seconds after it was sent.
        return count($recent) < $this->limit ? 0 : $recent[0] + $this->seconds - $now;
    }

    /** How many times are kept for a key: for the tests. */
    public function kept(string $key): int
    {
        return count($this->sent[$key] ?? []);
    }

    /** @return list<int> the key's times inside the window, and only those are kept */
    private function recent(string $key, int $now): array
    {
        $recent = array_values(array_filter($this->sent[$key] ?? [], fn(int $time): bool => $time > $now - $this->seconds));
        $this->sent[$key] = $recent;

        return $recent;
    }
}
