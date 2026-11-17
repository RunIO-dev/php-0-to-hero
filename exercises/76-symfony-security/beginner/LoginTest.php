<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once subject(__DIR__, 'login.php');

final class LoginTest extends TestCase
{
    private const string PASSWORD = 'tea kettle under the lemon tree';

    public function testTheHasherUsesCost12(): void
    {
        self::assertStringStartsWith('$2y$12$', memberHasher()->hash(self::PASSWORD));
    }

    public function testTheRightPasswordLogsIn(): void
    {
        $hasher = memberHasher();

        self::assertSame(['ok' => true, 'newHash' => null], logIn($hasher, $hasher->hash(self::PASSWORD), self::PASSWORD));
    }

    public function testAWrongOneDoesNot(): void
    {
        $hasher = memberHasher();
        $hash = $hasher->hash(self::PASSWORD);

        self::assertSame(['ok' => false, 'newHash' => null], logIn($hasher, $hash, 'tea kettle'));
        self::assertSame(['ok' => false, 'newHash' => null], logIn($hasher, $hash, $hash), 'The hash is not the password');
    }

    public function testAWeakHashIsMadeStrongerAtLogin(): void
    {
        $hasher = memberHasher();
        $old = password_hash(self::PASSWORD, PASSWORD_BCRYPT, ['cost' => 4]);

        $login = logIn($hasher, $old, self::PASSWORD);

        self::assertTrue($login['ok']);
        self::assertNotNull($login['newHash']);
        self::assertStringStartsWith('$2y$12$', $login['newHash']);
        self::assertTrue($hasher->verify($login['newHash'], self::PASSWORD));
    }

    public function testAWrongPasswordNeverGetsANewHash(): void
    {
        self::assertNull(logIn(memberHasher(), password_hash(self::PASSWORD, PASSWORD_BCRYPT, ['cost' => 4]), 'wrong')['newHash']);
    }
}
