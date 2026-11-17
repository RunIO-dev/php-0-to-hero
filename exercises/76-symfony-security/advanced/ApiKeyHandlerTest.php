<?php

declare(strict_types=1);

use Keys\ApiKey;
use Keys\ApiKeyHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

require_once subject(__DIR__, 'ApiKey.php');
require_once subject(__DIR__, 'ApiKeyHandler.php');

final class ApiKeyHandlerTest extends TestCase
{
    private const string READER = 'sk-reader-7f3a91c2';
    private const string WRITER = 'sk-writer-04be66d8';
    private const string OLD = 'sk-old-9c1d2e3f';

    public function testAKeyBecomesItsOwner(): void
    {
        $user = self::handler()->getUserBadgeFrom(self::READER)->getUser();

        self::assertSame('reports', $user->getUserIdentifier());
        self::assertSame(['ROLE_BOOKS_READ'], $user->getRoles());
    }

    public function testEachScopeIsARole(): void
    {
        $user = self::handler()->getUserBadgeFrom(self::WRITER)->getUser();

        self::assertSame('Jonas', $user->getUserIdentifier());
        self::assertSame(['ROLE_BOOKS_READ', 'ROLE_BOOKS_WRITE'], $user->getRoles());
    }

    public function testAnUnknownKeyIsRefused(): void
    {
        $this->expectException(BadCredentialsException::class);
        self::handler()->getUserBadgeFrom('sk-guess');
    }

    public function testTheHashIsNotTheKey(): void
    {
        $this->expectException(BadCredentialsException::class);
        self::handler()->getUserBadgeFrom(hash('sha256', self::READER));
    }

    public function testAnExpiredKeyIsRefused(): void
    {
        $this->expectException(BadCredentialsException::class);
        self::handler()->getUserBadgeFrom(self::OLD);
    }

    public function testAKeyEndsAtItsExpiry(): void
    {
        $this->expectException(BadCredentialsException::class);
        self::handler(new DateTimeImmutable('2027-01-01 00:00'))->getUserBadgeFrom(self::READER);
    }

    private static function handler(DateTimeImmutable $now = new DateTimeImmutable('2026-11-17 12:00')): ApiKeyHandler
    {
        return new ApiKeyHandler([
            new ApiKey(hash('sha256', self::READER), 'reports', ['books:read'], new DateTimeImmutable('2027-01-01 00:00')),
            new ApiKey(hash('sha256', self::WRITER), 'Jonas', ['books:read', 'books:write'], new DateTimeImmutable('2027-06-01')),
            new ApiKey(hash('sha256', self::OLD), 'Ana', ['books:read'], new DateTimeImmutable('2026-11-01')),
        ], $now);
    }
}
