<?php

declare(strict_types=1);

namespace Shelf;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * One of the API's tokens (chapter 69), as the database keeps it: only its hash, for one member,
 * until a date. Chapter 74: an entity. When the member goes, their tokens go with them.
 */
#[ORM\Entity]
#[ORM\Table(name: 'api_tokens')]
final class ApiToken
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column]
        public readonly string $hash,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        public readonly Member $member,
        #[ORM\Column]
        public readonly DateTimeImmutable $expires,
    ) {}
}
