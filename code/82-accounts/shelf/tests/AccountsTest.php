<?php

declare(strict_types=1);

namespace Shelf\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shelf\Accounts;
use Shelf\EmailInUse;
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

        self::assertSame('Amira', $this->accounts->authenticate('amira', self::PASSWORD)?->name);
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

    /** Chapter 82: a new reader, not confirmed, with a hash of their password. */
    public function testASignUpMakesAReaderWhoWaitsForTheirEmail(): void
    {
        $member = $this->accounts->signUp('  Ana Lima ', ' Ana@Example.org ', self::PASSWORD);

        self::assertSame(['Ana Lima', 'ana@example.org', Role::Reader, false], [$member->name, $member->email, $member->role, $member->confirmed]);
        self::assertSame($member, $this->accounts->authenticate('ana lima', self::PASSWORD));
        self::assertSame($member, $this->members->findByEmail('ANA@example.org'));
    }

    /** @return iterable<string, array{string, string, string, string}> name, email, password, message */
    public static function wrongSignUps(): iterable
    {
        yield 'a name of one letter' => ['A', 'a@example.org', self::PASSWORD, 'A name has 2 to 40'];
        yield 'a name with a tag' => ['<b>Ana</b>', 'a@example.org', self::PASSWORD, 'A name has 2 to 40'];
        yield 'a name that is taken' => ['JONAS', 'a@example.org', self::PASSWORD, 'The name JONAS is taken'];
        yield 'not an address' => ['Ana', 'ana at home', self::PASSWORD, 'not an email address'];
        yield 'a short password' => ['Ana', 'a@example.org', 'short', 'at least 15 characters'];
    }

    #[DataProvider('wrongSignUps')]
    public function testASignUpSaysWhatIsWrong(string $name, string $email, string $password, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $this->accounts->signUp($name, $email, $password);
    }

    public function testAnAddressHasOneAccount(): void
    {
        $this->accounts->signUp('Ana', 'ana@example.org', self::PASSWORD);

        $this->expectException(EmailInUse::class);
        $this->accounts->signUp('Ana Two', 'ANA@example.org', self::PASSWORD);
    }

    public function testAChangeOfPasswordNeedsTheCurrentOne(): void
    {
        $this->accounts->setPassword('Amira', self::PASSWORD);

        try {
            $this->accounts->changePassword('Amira', 'not the password', 'a whole new sentence of words');
            self::fail('Changed without the current password');
        } catch (InvalidArgumentException $e) {
            self::assertSame('The current password is wrong.', $e->getMessage());
        }
        $this->accounts->changePassword('Amira', self::PASSWORD, 'a whole new sentence of words');
        self::assertNotNull($this->accounts->authenticate('Amira', 'a whole new sentence of words'));
        self::assertNull($this->accounts->authenticate('Amira', self::PASSWORD));
    }
}
