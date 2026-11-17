<?php

declare(strict_types=1);

namespace Keys;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Turns the key in "Authorization: Bearer <key>" into a user, for an access_token firewall.
 * - The keys are stored hashed: find the ApiKey whose hash is hash("sha256", $key).
 * - An unknown key, or one whose expiry is not after $now, is refused with BadCredentialsException.
 * - The user is the key's owner, an InMemoryUser with one role per scope:
 *   "books:read" → ROLE_BOOKS_READ, "books:write" → ROLE_BOOKS_WRITE.
 */
final readonly class ApiKeyHandler implements AccessTokenHandlerInterface
{
    /** @param list<ApiKey> $keys */
    public function __construct(private array $keys, private \DateTimeImmutable $now) {}

    public function getUserBadgeFrom(#[\SensitiveParameter] string $accessToken): UserBadge
    {
        return new UserBadge($accessToken, static fn(string $owner): InMemoryUser => new InMemoryUser($owner, null)); // TODO
    }
}
