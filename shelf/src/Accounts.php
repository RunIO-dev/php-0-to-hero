<?php

declare(strict_types=1);

namespace Shelf;

use InvalidArgumentException;

/**
 * Members' passwords (chapter 61). Only a hash is stored, made by password_hash. A typed password is
 * checked with password_verify, and when PHP's settings for new hashes have changed, the hash is made
 * again, at the one moment the password itself is known.
 */
final readonly class Accounts
{
    public const int MIN_LENGTH = 15;

    /** bcrypt, PHP's default algorithm, reads only the first 72 bytes of a password. */
    public const int MAX_BYTES = 72;

    /** Checked when a member has no hash, so a wrong name takes as long as a wrong password. */
    private const string NO_ACCOUNT = '$2y$12$Xj8ScftODSaYvM/E.MvyZeZ.E8N.oqfE9/FuwuZpcbk.06HZc8E4m';

    /** @param array<string, int> $options for password_hash; the tests pass a low cost, to run fast */
    public function __construct(private MemberRepository $members, private array $options = []) {}

    public function setPassword(string $name, string $password): void
    {
        if (mb_strlen($password) < self::MIN_LENGTH) {
            throw new InvalidArgumentException('A password needs at least ' . self::MIN_LENGTH . ' characters. A few words together are long and easy to remember.');
        }
        if (strlen($password) > self::MAX_BYTES) {
            throw new InvalidArgumentException('A password can have at most ' . self::MAX_BYTES . ' bytes: 72 letters of English, fewer in other scripts.');
        }
        $this->members->changePasswordHash($name, password_hash($password, PASSWORD_DEFAULT, $this->options));
    }

    /** The member whose name and password these are, or null. */
    public function authenticate(string $name, string $password): ?Member
    {
        $hash = $this->members->passwordHash($name);
        if (!password_verify($password, $hash ?? self::NO_ACCOUNT) || $hash === null) {
            return null;
        }
        if (password_needs_rehash($hash, PASSWORD_DEFAULT, $this->options)) {
            $this->members->changePasswordHash($name, password_hash($password, PASSWORD_DEFAULT, $this->options));
        }

        return $this->members->find($name);
    }
}
