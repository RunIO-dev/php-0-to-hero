<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;

/**
 * Tokens for programs that use the API (chapter 69). A token is 32 random bytes: nobody can guess it,
 * so a fast hash, SHA-256, is enough to keep it safe in the database, and finds it again in one step.
 * Passwords need password_hash (chapter 61), because people choose them, and they can be guessed.
 */
final readonly class ApiTokens
{
    /** How long a token lasts. A program that needs longer asks for a new one. */
    public const int DAYS = 30;

    public function __construct(private TokenRepository $tokens, private MemberRepository $members) {}

    /**
     * A new token for the member. The token itself is returned once, and never stored.
     *
     * @return array{token: string, expires: DateTimeImmutable}
     */
    public function issue(string $name, DateTimeImmutable $now): array
    {
        $token = bin2hex(random_bytes(32));
        $expires = $now->modify('+' . self::DAYS . ' days');
        $this->tokens->add(self::hash($token), $name, $expires);

        return ['token' => $token, 'expires' => $expires];
    }

    /** The member this token belongs to, or null if it is unknown or expired. */
    public function member(string $token, DateTimeImmutable $now): ?Member
    {
        $name = $this->tokens->memberName(self::hash($token), $now);

        return $name === null ? null : $this->members->find($name);
    }

    public function revoke(string $token): void
    {
        $this->tokens->remove(self::hash($token));
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
