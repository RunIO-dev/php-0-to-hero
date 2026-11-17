<?php

declare(strict_types=1);

namespace Shelf\Web;

/** What a visitor's pages remember between requests. Values are strings. */
interface Session
{
    public function get(string $key): ?string;

    public function set(string $key, string $value): void;

    /** The value, removed: for a message that is shown once. */
    public function pull(string $key): ?string;

    /** A new session ID, with the same values: at every change of who the visitor is (chapters 50 and 62). */
    public function regenerate(): void;
}
