<?php

declare(strict_types=1);

namespace Shelf;

use InvalidArgumentException;

/**
 * Members' passwords (chapter 61). Only a hash is stored, made by password_hash. A typed password is
 * checked with password_verify, and when PHP's settings for new hashes have changed, the hash is made
 * again, at the one moment the password itself is known. Chapter 82: signing up, and changing a password.
 */
final readonly class Accounts
{
    public const int MIN_LENGTH = 15;

    /** bcrypt, PHP's default algorithm, reads only the first 72 bytes of a password. */
    public const int MAX_BYTES = 72;

    /** A name to sign up with (chapter 82): 2 to 40 letters of any script, digits, and a few marks. */
    private const string NAME = "/^[\\p{L}\\p{N}][\\p{L}\\p{N} .'-]{1,39}$/uD";

    /** Checked when a member has no hash, so a wrong name takes as long as a wrong password. */
    private const string NO_ACCOUNT = '$2y$12$Xj8ScftODSaYvM/E.MvyZeZ.E8N.oqfE9/FuwuZpcbk.06HZc8E4m';

    /** @param array<string, int> $options for password_hash; the tests pass a low cost, to run fast */
    public function __construct(private MemberRepository $members, private array $options = []) {}

    public function setPassword(string $name, string $password): void
    {
        self::checkPassword($password);
        $this->members->changePasswordHash($name, password_hash($password, PASSWORD_DEFAULT, $this->options));
    }

    /**
     * A reader signs up (chapter 82): a name that is free, an email address, and a password by the
     * rules above. The member can't log in until they use the link in the email the controller sends.
     *
     * @throws InvalidArgumentException with a message for the form
     * @throws EmailInUse when the address already has an account, which the page must not reveal
     */
    public function signUp(string $name, string $email, string $password, string $language = 'en'): Member
    {
        $name = trim($name);
        if (preg_match(self::NAME, $name) !== 1) {
            throw new Refused('A name has 2 to 40 letters, digits, spaces, dots, apostrophes or hyphens, and starts with a letter or digit.');
        }
        if (filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
            throw new Refused('This is not an email address.');
        }
        self::checkPassword($password);
        if ($this->members->find($name) !== null) {
            throw new Refused('The name {name} is taken. Please choose another one.', ['name' => $name]);
        }
        if ($this->members->findByEmail($email) !== null) {
            throw new EmailInUse('This address already has an account.');
        }
        $member = Member::signUp($name, $email);
        $member->speaks($language); // chapter 87: the language of the page they signed up on
        $this->members->add($member);
        $this->setPassword($name, $password);

        return $member;
    }

    /** A logged-in member changes their password, with the current one (chapter 82). */
    public function changePassword(string $name, string $current, string $new): void
    {
        $this->checkCurrent($name, $current);
        $this->setPassword($name, $new);
    }

    /**
     * Throws unless $password is the member's: before a change to the account, the password is asked
     * again, so a session someone else found open is not enough (chapter 82).
     */
    public function checkCurrent(string $name, string $password): void
    {
        if ($this->authenticate($name, $password) === null) {
            throw new Refused('The current password is wrong.');
        }
    }

    private static function checkPassword(string $password): void
    {
        if (mb_strlen($password) < self::MIN_LENGTH) {
            throw new Refused('A password needs at least {count} characters. A few words together are long and easy to remember.', ['count' => self::MIN_LENGTH]);
        }
        if (strlen($password) > self::MAX_BYTES) {
            throw new Refused('A password can have at most {count} bytes: 72 letters of English, fewer in other scripts.', ['count' => self::MAX_BYTES]);
        }
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
