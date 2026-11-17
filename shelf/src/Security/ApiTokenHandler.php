<?php

declare(strict_types=1);

namespace Shelf\Security;

use DateTimeImmutable;
use SensitiveParameter;
use Shelf\ApiTokens;
use Shelf\Member;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * The API's tokens (chapter 69), for Symfony's security (chapter 76): the access_token firewall
 * reads "Authorization: Bearer <token>", and asks this class whose token it is.
 */
final readonly class ApiTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private ApiTokens $tokens) {}

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        $member = $this->tokens->member($accessToken, new DateTimeImmutable())
            ?? throw new BadCredentialsException('Unknown or expired token.');

        return new UserBadge($member->name, static fn(): Member => $member);
    }
}
