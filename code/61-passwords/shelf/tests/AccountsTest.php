<?php

declare(strict_types=1);

namespace Shelf\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shelf\Accounts;
use Shelf\Member;
use Shelf\MemberNotFound;
use Shelf\Role;

final class AccountsTest extends TestCase
{
    private const string PASSWORD = 'tea kettle under the lemon tree';

    private InMemoryMemberRepository $members;
    private Accounts $accounts;

    protected function setUp(): void
    {
        $this->members = new InMemoryMemberRepository([new Member('Amira', Role::Reader), new Member('Jonas', Role::Staff)]);
        $this->accounts = new Accounts($this->members, ['cost' => 4]);
    }

    public function testOnlyAHashIsStored(): void
    {
        $this->accounts->setPassword('Amira', self::PASSWORD);

        $hash = (string) $this->members->passwordHash('Amira');
        self::assertStringNotContainsString(self::PASSWORD, $hash);
        self::assertTrue(password_verify(self::PASSWORD, $hash));
    }

    public function testTheRightPasswordFindsTheMember(): void
    {
        $this->accounts->setPassword('Amira', self::PASSWORD);

        self::assertEquals(new Member('Amira', Role::Reader), $this->accounts->authenticate('amira', self::PASSWORD));
    }

    public function testAWrongPasswordOrNameFindsNobody(): void
    {
        $this->accounts->setPassword('Amira', self::PASSWORD);

        self::assertNull($this->accounts->authenticate('Amira', 'Tea kettle under the lemon tree'));
        self::assertNull($this->accounts->authenticate('Jonas', self::PASSWORD), 'Jonas has no password yet');
        self::assertNull($this->accounts->authenticate('Nobody', self::PASSWORD));
    }

    public function testAShortPasswordIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at least 15 characters');

        $this->accounts->setPassword('Amira', 'sunflower');
    }

    public function testAPasswordOver72BytesIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('at most 72 bytes');

        $this->accounts->setPassword('Amira', str_repeat('é', 37)); // 37 letters, 74 bytes
    }

    public function testOnlyAMemberGetsAPassword(): void
    {
        $this->expectException(MemberNotFound::class);

        $this->accounts->setPassword('Nobody', self::PASSWORD);
    }

    public function testAnOldHashIsMadeAgainAtLogin(): void
    {
        $old = password_hash(self::PASSWORD, PASSWORD_BCRYPT, ['cost' => 5]);
        $this->members->changePasswordHash('Amira', $old);

        $this->accounts->authenticate('Amira', self::PASSWORD);

        $new = (string) $this->members->passwordHash('Amira');
        self::assertNotSame($old, $new);
        self::assertFalse(password_needs_rehash($new, PASSWORD_DEFAULT, ['cost' => 4]));
        self::assertTrue(password_verify(self::PASSWORD, $new));
    }
}
