<?php

declare(strict_types=1);

namespace Shelf\Tests\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shelf\ApiTokens;
use Shelf\Member;
use Shelf\Role;
use Shelf\Security\ApiTokenHandler;
use Shelf\Tests\InMemoryMemberRepository;
use Shelf\Tests\InMemoryTokenRepository;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

/** The API's tokens for Symfony's security (chapter 76): a token finds its member, any other is refused. */
final class ApiTokenHandlerTest extends TestCase
{
    private ApiTokens $tokens;

    protected function setUp(): void
    {
        $members = new InMemoryMemberRepository([new Member('Amira', Role::Reader)]);
        $this->tokens = new ApiTokens(new InMemoryTokenRepository($members), $members);
    }

    public function testATokenIsItsMembersBadge(): void
    {
        $token = $this->tokens->issue('Amira', new DateTimeImmutable())['token'];

        $badge = new ApiTokenHandler($this->tokens)->getUserBadgeFrom($token);

        self::assertSame('Amira', $badge->getUserIdentifier());
        self::assertSame('Amira', $badge->getUser()->getUserIdentifier());
    }

    public function testAnUnknownTokenIsRefused(): void
    {
        $this->expectException(BadCredentialsException::class);

        new ApiTokenHandler($this->tokens)->getUserBadgeFrom(str_repeat('0', 64));
    }
}
