<?php

declare(strict_types=1);

namespace Club;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * A member of the reading club, as a Doctrine entity in the table "members":
 * - id: a number the database gives each new member;
 * - name: text, unique: no two members with the same name;
 * - email: text, or null for a member without one;
 * - joined: the day they joined, a date without a time (date_immutable).
 */
#[ORM\Entity]
#[ORM\Table(name: 'members')]
final class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    public function __construct(
        #[ORM\Column(unique: true)]
        public readonly string $name,
        #[ORM\Column(type: 'date_immutable')]
        public readonly DateTimeImmutable $joined,
        #[ORM\Column(nullable: true)]
        public readonly ?string $email = null,
    ) {}
}
