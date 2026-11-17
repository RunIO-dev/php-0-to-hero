<?php

declare(strict_types=1);

namespace Desk;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'loans')]
final class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) ?int $id = null;

    public function __construct(
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false)]
        public readonly Book $book,
        #[ORM\Column]
        public readonly string $member,
        #[ORM\Column(type: 'date_immutable')]
        public readonly DateTimeImmutable $due,
    ) {}
}
