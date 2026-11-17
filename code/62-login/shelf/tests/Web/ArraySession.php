<?php

declare(strict_types=1);

namespace Shelf\Tests\Web;

use Shelf\Web\Session;

/** A session for tests: an array, with no cookie and no server. */
final class ArraySession implements Session
{
    /** How often the session got a new ID. */
    public int $regenerated = 0;

    /** @param array<string, string> $values */
    public function __construct(public array $values = []) {}

    public function get(string $key): ?string
    {
        return $this->values[$key] ?? null;
    }

    public function set(string $key, string $value): void
    {
        $this->values[$key] = $value;
    }

    public function pull(string $key): ?string
    {
        $value = $this->get($key);
        unset($this->values[$key]);

        return $value;
    }

    public function regenerate(): void
    {
        $this->regenerated++;
    }
}
