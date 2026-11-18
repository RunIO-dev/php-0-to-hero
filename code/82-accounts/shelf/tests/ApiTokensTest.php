<?php

declare(strict_types=1);

namespace Shelf\Tests;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Shelf\ApiTokens;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\Role;

final class ApiTokensTest extends TestCase
{
    private InMemoryTokenRepository $rows;
    private ApiTokens $tokens;

    protected function setUp(): void
    {
        $members = new InMemoryMemberRepository([new Member('Amira', Role::Reader)]);
        $this->rows = new InMemoryTokenRepository($members);
        $this->tokens = new ApiTokens($this->rows, $members);
    }

    public function testATokenIsRandomAndOnlyItsHashIsKept(): void
    {
        $now = new DateTimeImmutable('2026-03-02 10:00 UTC');

        $first = $this->tokens->issue('amira', $now);
        $second = $this->tokens->issue('Amira', $now);

        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $first['token']);
        self::assertNotSame($first['token'], $second['token']);
        self::assertSame([hash('sha256', $first['token']), hash('sha256', $second['token'])], array_keys($this->rows->rows));
        self::assertEquals(new DateTimeImmutable('2026-04-01 10:00 UTC'), $first['expires']);
    }

    public function testATokenFindsItsMemberUntilItExpires(): void
    {
        $token = $this->tokens->issue('Amira', new DateTimeImmutable('2026-03-02 10:00 UTC'))['token'];

        self::assertSame('Amira', $this->tokens->member($token, new DateTimeImmutable('2026-04-01 09:59 UTC'))?->name);
        self::assertNull($this->tokens->member($token, new DateTimeImmutable('2026-04-01 10:00 UTC')));
        self::assertNull($this->tokens->member('not-a-token', new DateTimeImmutable('2026-03-02 10:00 UTC')));
    }

    public function testARevokedTokenFindsNobody(): void
    {
        $now = new DateTimeImmutable('2026-03-02 10:00 UTC');
        $token = $this->tokens->issue('Amira', $now)['token'];

        $this->tokens->revoke($token);

        self::assertNull($this->tokens->member($token, $now));
    }

    public function testOnlyAMemberGetsAToken(): void
    {
        $this->expectException(MemberNotFound::class);

        $this->tokens->issue('Nobody', new DateTimeImmutable());
    }
}
