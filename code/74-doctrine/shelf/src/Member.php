<?php

declare(strict_types=1);

namespace Shelf;

use Doctrine\ORM\Mapping as ORM;
use UnexpectedValueException;

/**
 * Someone who may borrow books. Chapter 74: an entity, with the password hash of chapter 61, which
 * only MemberRepository reads and changes.
 */
#[ORM\Entity]
#[ORM\Table(name: 'members')]
final class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    private ?string $passwordHash = null;

    public function __construct(
        #[ORM\Column(unique: true, options: ['collation' => 'NOCASE'])]
        public readonly string $name,
        #[ORM\Column(enumType: Role::class)]
        public readonly Role $role,
    ) {}

    /** A member from the data in members.json, checked. */
    public static function fromArray(mixed $data): self
    {
        $role = is_array($data) && is_string($data['role'] ?? null) ? Role::tryFrom($data['role']) : null;
        if (!is_array($data) || !is_string($data['name'] ?? null) || $role === null) {
            throw new UnexpectedValueException('Each member needs a name and a role, reader or staff: ' . json_encode($data));
        }

        return new self($data['name'], $role);
    }
}
