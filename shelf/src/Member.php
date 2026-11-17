<?php

declare(strict_types=1);

namespace Shelf;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use UnexpectedValueException;

/**
 * Someone who may borrow books. Chapter 74: an entity, with the password hash of chapter 61, which
 * MemberRepository changes. Chapter 76: a user for Symfony's security, which logs them in by their
 * name and password, and gives them the roles of their Role.
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

    /** @param non-empty-string $name what a member logs in with, so never empty (chapter 76) */
    public function __construct(
        #[ORM\Column(unique: true, options: ['collation' => 'NOCASE'])]
        public readonly string $name,
        #[ORM\Column(enumType: Role::class)]
        public readonly Role $role,
    ) {}

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
