<?php

declare(strict_types=1);

namespace Shelf;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Someone who may borrow books. Chapter 74: an entity, with the password hash of chapter 61, which
 * MemberRepository changes. Chapter 76: a user for Symfony's security, which logs them in by their
 * name and password, and gives them the roles of their Role. Chapter 82: an email address, confirmed
 * by a link, for members who sign up themselves.
 */
#[ORM\Entity]
#[ORM\Table(name: 'members')]
final class Member implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $passwordHash = null;

    /** Chapter 82: where Shelf sends links; null for the members of members.json, until they add one. */
    #[ORM\Column(nullable: true, unique: true, options: ['collation' => 'NOCASE'])]
    public private(set) ?string $email = null;

    /** Chapter 82: false from sign-up until the link in the first email is used; until then, no login. */
    #[ORM\Column(options: ['default' => true])]
    public private(set) bool $confirmed = true;

    /** @param non-empty-string $name what a member logs in with, so never empty (chapter 76) */
    public function __construct(
        #[ORM\Column(unique: true, options: ['collation' => 'NOCASE'])]
        public readonly string $name,
        #[ORM\Column(enumType: Role::class)]
        public readonly Role $role,
    ) {}

    /** The row's number (chapter 82: in the links of emails); the database gives it when the member is stored. */
    public function id(): int
    {
        return $this->id ?? throw new \LogicException("$this->name isn't stored yet");
    }

    /** A reader who signed up themselves (chapter 82), not confirmed yet. */
    public static function signUp(string $name, string $email): self
    {
        if ($name === '') {
            throw new InvalidArgumentException('A member needs a name');
        }
        $member = new self($name, Role::Reader);
        $member->email = self::normalEmail($email);
        $member->confirmed = false;

        return $member;
    }

    /** The link in an email was used: this address is the member's, and they may log in. */
    public function confirmEmail(string $email): void
    {
        $this->email = self::normalEmail($email);
        $this->confirmed = true;
    }

    /** The form that addresses are kept in: trimmed, in lower case, so one address is one account. */
    public static function normalEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public function getUserIdentifier(): string
    {
        return $this->name;
    }

    /** @return list<string> ROLE_READER or ROLE_STAFF; security.yaml says staff are readers too */
    public function getRoles(): array
    {
        return ['ROLE_' . strtoupper($this->role->value)];
    }

    /** The hash, never the password: Symfony checks a typed password against it. */
    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    /** Symfony 7 still asks for it; a Member never holds a typed password, so there is nothing to erase. */
    #[\Deprecated]
    public function eraseCredentials(): void {}

    /** A member from the data in members.json, checked. */
    public static function fromArray(mixed $data): self
    {
        $role = is_array($data) && is_string($data['role'] ?? null) ? Role::tryFrom($data['role']) : null;
        if (!is_array($data) || !is_string($data['name'] ?? null) || $data['name'] === '' || $role === null) {
            throw new UnexpectedValueException('Each member needs a name and a role, reader or staff: ' . json_encode($data));
        }

        return new self($data['name'], $role);
    }
}
