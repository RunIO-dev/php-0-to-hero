<?php

declare(strict_types=1);

// Exercise 50, advanced: flash messages of two kinds, several at once.
//
// add($type, $text): keep a message for the next page. $type is 'success' or 'error'; any other
//   type is an InvalidArgumentException.
// all(): every message added so far, in order, as ['type' => ..., 'text' => ...], and removes
//   them: a second all() returns [].
// The session stores strings only: keep the messages as JSON under the key 'flash'. If that value
// is broken (not JSON, or not a list of messages), all() returns [] instead of failing: the
// session is data from earlier requests, and may be from an older version of the code.

require_once __DIR__ . '/Session.php';

final readonly class Flash
{
    public function __construct(private Session $session) {}

    public function add(string $type, string $text): void {}

    /** @return list<array{type: string, text: string}> */
    public function all(): array
    {
        return [];
    }
}
// Check your work: composer exercise 50 advanced
