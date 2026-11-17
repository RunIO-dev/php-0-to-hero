<?php

declare(strict_types=1);

/**
 * A new token for the API: 32 random bytes, as 64 hexadecimal characters, and the hash the database
 * keeps, its SHA-256. The token goes to the member once; only the hash is stored.
 *
 * @return array{token: string, hash: string}
 */
function newToken(): array
{
    $token = bin2hex(random_bytes(32));

    return ['token' => $token, 'hash' => hash('sha256', $token)];
}

/**
 * The member a token belongs to, or null.
 *
 * @param array<string, string> $hashes what the database holds: a token's hash => the member's name
 */
function memberFor(string $token, array $hashes): ?string
{
    return $hashes[hash('sha256', $token)] ?? null;
}
