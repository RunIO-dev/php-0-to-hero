<?php

declare(strict_types=1);

namespace Keys;

/** A stored API key: only the SHA-256 hash of the key, never the key itself. */
final readonly class ApiKey
{
    /** @param list<string> $scopes such as 'books:read', 'books:write' */
    public function __construct(
        public string $hash,
        public string $owner,
        public array $scopes,
        public \DateTimeImmutable $expires,
    ) {}
}
